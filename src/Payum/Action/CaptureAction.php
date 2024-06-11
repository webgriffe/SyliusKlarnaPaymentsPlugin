<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\ClientInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\PaymentSessionStatus;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\ApiContext;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Authorization;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\HostedPaymentPage;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payment;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\HostedPaymentPageSession;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSession;
use Webgriffe\SyliusKlarnaPlugin\Controller\PaymentController;
use Webgriffe\SyliusKlarnaPlugin\Model\PaymentDetails;
use Webgriffe\SyliusKlarnaPlugin\PaymentDetailsHelper;
use Webgriffe\SyliusKlarnaPlugin\Payum\KlarnaPaymentsApi;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\Api\CreateHostedPaymentPageSession;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\Api\CreatePaymentSession;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\ConvertSyliusPaymentToKlarnaHostedPaymentPage;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\ConvertSyliusPaymentToKlarnaPayment;
use Webmozart\Assert\Assert;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @psalm-import-type StoredPaymentDetails from PaymentDetails
 */
final class CaptureAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait, GenericTokenFactoryAwareTrait, ApiAwareTrait;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly LoggerInterface $logger,
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
    ) {
        $this->apiClass = KlarnaPaymentsApi::class;
    }

    /**
     * This action is invoked by two main entry points:
     *  - Starting the payment.
     *      Assuming that the payment details are empty because it is the first attempt to pay, we proceed by creating
     *      the Klarna Payment Session. This session should still be opened during all the checkout on the gateway.
     *  - Returning after Klarna checkout.
     *      We should follow this case by catching any query parameters on the request
     *
     * @param Capture|mixed $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        Assert::isInstanceOf($request, Capture::class);

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getModel();

        $captureToken = $request->getToken();
        Assert::isInstanceOf($captureToken, TokenInterface::class);

        $klarnaPaymentsApi = $this->api;
        Assert::isInstanceOf($klarnaPaymentsApi, KlarnaPaymentsApi::class);

        $storedPaymentDetails = $payment->getDetails();
        if ($storedPaymentDetails === []) {
            $paymentDetails = $this->createPaymentSession($payment);
        } else {
            if (!PaymentDetailsHelper::areValid($storedPaymentDetails)) {
                throw new RuntimeException('Payment details are already populated with others data. Maybe this payment should be marked as error');
            }
            $paymentDetails = PaymentDetails::createFromStoredPaymentDetails($storedPaymentDetails);
        }

        if ($paymentDetails->getPaymentSessionStatus() === PaymentSessionStatus::Complete) {
            $session = $this->requestStack->getSession();
            $session->set(PaymentController::PAYMENT_ID_SESSION_KEY, $payment->getId());
            $session->set(PaymentController::TOKEN_HASH_SESSION_KEY, $captureToken->getHash());

            $order = $payment->getOrder();
            Assert::isInstanceOf($order, OrderInterface::class);

            throw new HttpRedirect(
                $this->router->generate('webgriffe_sylius_klarna_plugin.payment.process', [
                    'tokenValue' => $order->getTokenValue(),
                ]),
            );
        }

        if ($paymentDetails->getHostedPaymentPageId() === null) {
            $this->createHostedPaymentPageSession(
                $paymentDetails,
                $captureToken,
            );
        }

        $hostedPaymentPageRedirectUrl = $paymentDetails->getHostedPaymentPageRedirectUrl();
        Assert::stringNotEmpty($hostedPaymentPageRedirectUrl);

        throw new HttpRedirect($hostedPaymentPageRedirectUrl);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof SyliusPaymentInterface
        ;
    }

    private function createPaymentSession(
        SyliusPaymentInterface $payment,
    ): PaymentDetails {
        $convertSyliusPaymentToKlarnaPayment = new ConvertSyliusPaymentToKlarnaPayment(
            $payment,
            null,
            null,
            null,
            null,
        );
        $this->gateway->execute($convertSyliusPaymentToKlarnaPayment);
        $klarnaPayment = $convertSyliusPaymentToKlarnaPayment->getKlarnaPayment();
        Assert::isInstanceOf($klarnaPayment, Payment::class);

        $createPaymentSession = new CreatePaymentSession($klarnaPayment);
        $this->gateway->execute($createPaymentSession);
        $paymentSession = $createPaymentSession->getPaymentSession();
        Assert::isInstanceOf($paymentSession, PaymentSession::class);

        $this->logger->debug(sprintf('Created Klarna Payment Session with ID: %s', $paymentSession->getSessionId()));

        return PaymentDetails::createFromPaymentSession($paymentSession);
    }

    private function createHostedPaymentPageSession(
        PaymentDetails $paymentDetails,
        TokenInterface $captureToken,
    ): void {
        $klarnaPaymentsApi = $this->api;
        Assert::isInstanceOf($klarnaPaymentsApi, KlarnaPaymentsApi::class);

        $apiContext = new ApiContext(
            new Authorization($klarnaPaymentsApi->getUsername(), $klarnaPaymentsApi->getPassword()),
            $klarnaPaymentsApi->getServerRegion(),
            $klarnaPaymentsApi->isSandBox(),
        );
        $cancelToken = $this->tokenFactory->createToken(
            $captureToken->getGatewayName(),
            $captureToken->getDetails(),
            'payum_cancel_do',
            [],
            $captureToken->getAfterUrl(),
        );
        $cancelUrl = $cancelToken->getTargetUrl();
        $notifyToken = $this->tokenFactory->createNotifyToken(
            $captureToken->getGatewayName(),
            $captureToken->getDetails(),
        );
        $notifyUrl = $notifyToken->getTargetUrl();

        $convertSyliusPaymentToKlarnaHostedPaymentPage = new ConvertSyliusPaymentToKlarnaHostedPaymentPage(
            $captureToken->getTargetUrl(),
            $notifyUrl,
            $cancelUrl,
            $cancelUrl,
            $cancelUrl,
            $cancelUrl,
            $this->client->createPaymentSessionUrl($apiContext, $paymentDetails->getPaymentSessionId()),
        );
        $this->gateway->execute($convertSyliusPaymentToKlarnaHostedPaymentPage);
        $klarnaHostedPaymentPage = $convertSyliusPaymentToKlarnaHostedPaymentPage->getKlarnaHostedPaymentPage();
        Assert::isInstanceOf($klarnaHostedPaymentPage, HostedPaymentPage::class);

        $createHostedPaymentPageSession = new CreateHostedPaymentPageSession($klarnaHostedPaymentPage);
        $this->gateway->execute($createHostedPaymentPageSession);
        $hostedPaymentPageSession = $createHostedPaymentPageSession->getHostedPaymentPageSession();
        Assert::isInstanceOf($hostedPaymentPageSession, HostedPaymentPageSession::class);

        $this->logger->debug(sprintf('Created Klarna Hosted Payment Page Session with ID: %s', $hostedPaymentPageSession->getSessionId()));

        $paymentDetails->setHostedPaymentPageId($hostedPaymentPageSession->getSessionId());
        $paymentDetails->setHostedPaymentPageRedirectUrl($hostedPaymentPageSession->getRedirectUrl());
    }
}
