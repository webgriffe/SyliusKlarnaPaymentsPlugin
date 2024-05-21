<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Action;

use DateTimeImmutable;
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
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\ClientInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\PaymentSessionStatus;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\ApiContext;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Authorization;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\HostedPaymentPage;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payment;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\HostedPaymentPageSession;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSession;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSessionDetails;
use Webgriffe\SyliusKlarnaPlugin\PaymentDetailsHelper;
use Webgriffe\SyliusKlarnaPlugin\Payum\KlarnaPaymentsApi;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\Api\CreateHostedPaymentPageSession;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\Api\CreatePaymentSession;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\Api\ReadPaymentSession;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\ConvertSyliusPaymentToKlarnaHostedPaymentPage;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\ConvertSyliusPaymentToKlarnaPayment;
use Webmozart\Assert\Assert;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @psalm-import-type PaymentDetails from \Webgriffe\SyliusKlarnaPlugin\PaymentDetailsHelper
 */
final class CaptureAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait, GenericTokenFactoryAwareTrait, ApiAwareTrait;

    public function __construct(
        private readonly ClientInterface $client,
    ) {
        $this->apiClass = KlarnaPaymentsApi::class;
    }

    /**
     * This action is invoked by two main entry points:
     *  - Starting the payment.
     *      Assuming that the payment details are empty because it is the first attempt to pay, we proceed by creating
     *      the Klarna Payment Session. This session should still be opened during all the checkout on the gateway.
     *
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

        /**
         * @TODO improve this
         */
        $captureUrl = $captureToken->getTargetUrl();
        $captureUrl .= '?authorization_token={{authorization_token}}';

        $klarnaPaymentsApi = $this->api;
        Assert::isInstanceOf($klarnaPaymentsApi, KlarnaPaymentsApi::class);

        $paymentSessionJustCreated = false;
        if ($payment->getDetails() === []) {
            $this->createPaymentSession($payment);
            $paymentSessionJustCreated = true;
        }
        /** @var PaymentDetails $paymentDetails */
        $paymentDetails = $payment->getDetails();
        PaymentDetailsHelper::assertPaymentDetailsAreValid($paymentDetails);
        $paymentSession = PaymentDetailsHelper::extractPaymentSessionFromPaymentDetails($paymentDetails);

        if (!$paymentSessionJustCreated) {
            $this->checkIfPaymentSessionIsStillValid($paymentSession);
        }

        if (!PaymentDetailsHelper::haveHostedPaymentPageSessionData($paymentDetails)) {
            $this->createHostedPaymentPageSession(
                $captureToken,
                $captureUrl,
                $paymentSession,
                $payment,
            );
        }
        /** @var PaymentDetails $paymentDetails */
        $paymentDetails = $payment->getDetails();
        PaymentDetailsHelper::assertPaymentDetailsAreValid($paymentDetails);
        $hostedPaymentPageSession = PaymentDetailsHelper::extractHostedPaymentPageSessionFromPaymentDetails($paymentDetails);

        throw new HttpRedirect($hostedPaymentPageSession->getRedirectUrl());
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof SyliusPaymentInterface
        ;
    }

    private function createPaymentSession(SyliusPaymentInterface $payment): void
    {
        $convertSyliusPaymentToKlarnaPayment = new ConvertSyliusPaymentToKlarnaPayment($payment);
        $this->gateway->execute($convertSyliusPaymentToKlarnaPayment);
        $klarnaPayment = $convertSyliusPaymentToKlarnaPayment->getKlarnaPayment();
        Assert::isInstanceOf($klarnaPayment, Payment::class);

        $createPaymentSession = new CreatePaymentSession($klarnaPayment);
        $this->gateway->execute($createPaymentSession);
        $paymentSession = $createPaymentSession->getPaymentSession();
        Assert::isInstanceOf($paymentSession, PaymentSession::class);
        $payment->setDetails(
            PaymentDetailsHelper::storePaymentSessionOnPaymentDetails($paymentSession),
        );
    }

    private function checkIfPaymentSessionIsStillValid(PaymentSession $paymentSession): void
    {
        $createPaymentSession = new ReadPaymentSession($paymentSession->getSessionId());
        $this->gateway->execute($createPaymentSession);
        $paymentSessionDetails = $createPaymentSession->getPaymentSessionDetails();
        Assert::isInstanceOf($paymentSessionDetails, PaymentSessionDetails::class);

        if ($paymentSessionDetails->getStatus() !== PaymentSessionStatus::Incomplete) {
            throw new \RuntimeException('TODO: order already placed');
        }
        if (new DateTimeImmutable('now') >= $paymentSessionDetails->getExpiresAt()) {
            throw new \RuntimeException('TODO: session expired');
        }
    }

    private function createHostedPaymentPageSession(
        TokenInterface $captureToken,
        string $captureUrl,
        PaymentSession $paymentSession,
        SyliusPaymentInterface $payment,
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
            $captureUrl,
            $notifyUrl,
            $cancelUrl,
            $this->client->createPaymentSessionUrl($apiContext, $paymentSession->getSessionId()),
        );
        $this->gateway->execute($convertSyliusPaymentToKlarnaHostedPaymentPage);
        $klarnaHostedPaymentPage = $convertSyliusPaymentToKlarnaHostedPaymentPage->getKlarnaHostedPaymentPage();
        Assert::isInstanceOf($klarnaHostedPaymentPage, HostedPaymentPage::class);

        $createHostedPaymentPageSession = new CreateHostedPaymentPageSession($klarnaHostedPaymentPage);
        $this->gateway->execute($createHostedPaymentPageSession);
        $hostedPaymentPageSession = $createHostedPaymentPageSession->getHostedPaymentPageSession();
        Assert::isInstanceOf($hostedPaymentPageSession, HostedPaymentPageSession::class);

        /** @var PaymentDetails $paymentDetails */
        $paymentDetails = $payment->getDetails();

        $payment->setDetails(
            PaymentDetailsHelper::storeHostedPaymentPageSessionOnPaymentDetails(
                $paymentDetails,
                $hostedPaymentPageSession,
            ),
        );
    }
}
