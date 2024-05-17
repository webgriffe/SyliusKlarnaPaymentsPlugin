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
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\ClientInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\ApiContext;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Authorization;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\HostedPaymentPage;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payment;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\HostedPaymentPageSession;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSession;
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

        if ($payment->getDetails() === []) {
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
        } else {
            /** @var PaymentDetails $paymentDetails */
            $paymentDetails = $payment->getDetails();
            PaymentDetailsHelper::assertPaymentDetailsAreValid($paymentDetails);
            $paymentSession = PaymentDetailsHelper::extractPaymentSessionFromPaymentDetails($paymentDetails);
        }
        /** @var PaymentDetails $paymentDetails */
        $paymentDetails = $payment->getDetails();

        if (!PaymentDetailsHelper::haveHostedPaymentPageSessionData($paymentDetails)) {
            $apiContext = new ApiContext(
                new Authorization($klarnaPaymentsApi->getUsername(), $klarnaPaymentsApi->getPassword()),
                $klarnaPaymentsApi->getServerRegion(),
                $klarnaPaymentsApi->isSandBox(),
            );
            $cancelToken = $this->tokenFactory->createToken($captureToken->getGatewayName(), $captureToken->getDetails(), 'payum_cancel_do', [], $captureToken->getAfterUrl());
            $cancelUrl = $cancelToken->getTargetUrl();

            $notifyToken = $this->tokenFactory->createNotifyToken($captureToken->getGatewayName(), $captureToken->getDetails());
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

            $payment->setDetails(
                PaymentDetailsHelper::storeHostedPaymentPageSessionOnPaymentDetails($paymentDetails, $hostedPaymentPageSession),
            );
        } else {
            PaymentDetailsHelper::assertPaymentDetailsAreValid($paymentDetails);
            $hostedPaymentPageSession = PaymentDetailsHelper::extractHostedPaymentPageSessionFromPaymentDetails($paymentDetails);
        }

        throw new HttpRedirect($hostedPaymentPageSession->getRedirectUrl());
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof SyliusPaymentInterface
        ;
    }
}
