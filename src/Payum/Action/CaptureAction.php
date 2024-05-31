<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Action;

use DateTimeImmutable;
use InvalidArgumentException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\ClientInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\HostedPaymentPageSessionStatus;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\PaymentSessionStatus;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\ApiContext;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Authorization;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\HostedPaymentPage;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Order;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payment;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\HostedPaymentPageSession;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\HostedPaymentPageSessionDetails;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\Order as OrderResponse;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\OrderDetails;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSession;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSessionDetails;
use Webgriffe\SyliusKlarnaPlugin\PaymentDetailsHelper;
use Webgriffe\SyliusKlarnaPlugin\Payum\KlarnaPaymentsApi;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\Api\CreateHostedPaymentPageSession;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\Api\CreateOrder;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\Api\CreatePaymentSession;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\Api\ReadHostedPaymentPageSession;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\Api\ReadOrder;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\Api\ReadPaymentSession;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\ConvertSyliusPaymentToKlarnaHostedPaymentPage;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\ConvertSyliusPaymentToKlarnaOrder;
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
        private readonly LoggerInterface $logger,
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

        // This is needed to populate the http request with GET and POST params from current request
        $getHttpRequest = new GetHttpRequest();
        $this->gateway->execute($getHttpRequest);
        if ($this->areWeInTheConsumerRedirectionUrl($getHttpRequest)) {
            $this->handlePaymentProcessed($payment, $getHttpRequest);

            return;
        }

        // We are just starting the payment, so continue to launch it!

        $paymentSessionJustCreated = false;
        if ($payment->getDetails() === []) {
            $this->createPaymentSession($payment, $captureToken);
            $paymentSessionJustCreated = true;
        }
        /** @var PaymentDetails $paymentDetails */
        $paymentDetails = $payment->getDetails();
        PaymentDetailsHelper::assertPaymentDetailsAreValid($paymentDetails);
        $paymentSession = PaymentDetailsHelper::extractPaymentSessionFromPaymentDetails($paymentDetails);

        if (!$paymentSessionJustCreated) {
            try {
                $this->checkIfPaymentSessionIsStillValid($paymentSession);
            } catch (RuntimeException $e) {
                // TODO Catch better exception
                $this->logger->debug('Payment session is not valid anymore. Fail current payment.');

                return;
            }
        }

        $hostedPaymentPageSessionJustCreated = false;
        if (!PaymentDetailsHelper::haveHostedPaymentPageSessionData($paymentDetails)) {
            $this->createHostedPaymentPageSession(
                $captureToken,
                $paymentSession,
                $payment,
            );
            $hostedPaymentPageSessionJustCreated = true;
        }
        /** @var PaymentDetails $paymentDetails */
        $paymentDetails = $payment->getDetails();
        PaymentDetailsHelper::assertPaymentDetailsAreValid($paymentDetails);
        $hostedPaymentPageSession = PaymentDetailsHelper::extractHostedPaymentPageSessionFromPaymentDetails($paymentDetails);

        if (!$hostedPaymentPageSessionJustCreated) {
            $this->checkIfHostedPaymentPageSessionIsStillValid($hostedPaymentPageSession);
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

    private function createPaymentSession(
        SyliusPaymentInterface $payment,
        TokenInterface $captureToken,
    ): void {
        $notifyToken = $this->tokenFactory->createNotifyToken(
            $captureToken->getGatewayName(),
            $captureToken->getDetails(),
        );
        $notifyUrl = $notifyToken->getTargetUrl();

        $convertSyliusPaymentToKlarnaPayment = new ConvertSyliusPaymentToKlarnaPayment(
            $payment,
            $captureToken->getTargetUrl(),
            $notifyUrl,
        );
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

    /**
     * @throws RuntimeException
     */
    private function checkIfPaymentSessionIsStillValid(PaymentSession $paymentSession): void
    {
        $readPaymentSession = new ReadPaymentSession($paymentSession->getSessionId());
        $this->gateway->execute($readPaymentSession);
        $paymentSessionDetails = $readPaymentSession->getPaymentSessionDetails();
        Assert::isInstanceOf($paymentSessionDetails, PaymentSessionDetails::class);

        if ($paymentSessionDetails->getStatus() !== PaymentSessionStatus::Incomplete) {
            $this->logger->debug('Order already placed');

            throw new RuntimeException('TODO: order already placed');
        }
        if (new DateTimeImmutable('now') >= $paymentSessionDetails->getExpiresAt()) {
            $this->logger->debug('Session expired');

            throw new RuntimeException('TODO: session expired');
        }
    }

    private function createHostedPaymentPageSession(
        TokenInterface $captureToken,
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
            $captureToken->getTargetUrl(),
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

    private function checkIfHostedPaymentPageSessionIsStillValid(HostedPaymentPageSession $hostedPaymentPageSession): void
    {
        $readHostedPaymentPageSession = new ReadHostedPaymentPageSession($hostedPaymentPageSession->getSessionId());
        $this->gateway->execute($readHostedPaymentPageSession);
        $hostedPaymentPageSessionDetails = $readHostedPaymentPageSession->getHostedPaymentPageSessionDetails();
        Assert::isInstanceOf($hostedPaymentPageSessionDetails, HostedPaymentPageSessionDetails::class);

        if ($hostedPaymentPageSessionDetails->getStatus() === HostedPaymentPageSessionStatus::Completed) {
            throw new RuntimeException('TODO: HPP already placed');
        }
        if (new DateTimeImmutable('now') >= $hostedPaymentPageSessionDetails->getExpiresAt()) {
            throw new RuntimeException('TODO: HPP expired');
        }
    }

    private function areWeInTheConsumerRedirectionUrl(GetHttpRequest $getHttpRequest): bool
    {
        $queryParameters = $getHttpRequest->query;
        if ($queryParameters === []) {
            return false;
        }
        if (array_key_exists(HostedPaymentPage::ORDER_ID_KEY, $queryParameters)) {
            /** @var string|mixed $orderId */
            $orderId = $queryParameters[HostedPaymentPage::ORDER_ID_KEY];
            if (is_string($orderId) && $orderId !== '' && $orderId !== '{{order_id}}') {
                return true;
            }
        }
        if (array_key_exists(HostedPaymentPage::AUTHORIZATION_TOKEN_KEY, $queryParameters)) {
            /** @var string|mixed $authorizationToken */
            $authorizationToken = $queryParameters[HostedPaymentPage::AUTHORIZATION_TOKEN_KEY];
            if (is_string($authorizationToken) && $authorizationToken !== '' && $authorizationToken !== '{{authorization_token}}') {
                return true;
            }
        }

        return false;
    }

    private function handlePaymentProcessed(SyliusPaymentInterface $payment, GetHttpRequest $getHttpRequest): void
    {
        $queryParameters = $getHttpRequest->query;
        /** @var PaymentDetails $paymentDetails */
        $paymentDetails = $payment->getDetails();
        PaymentDetailsHelper::assertPaymentDetailsAreValid($paymentDetails);
        $hostedPaymentPageSession = PaymentDetailsHelper::extractHostedPaymentPageSessionFromPaymentDetails($paymentDetails);
        if (array_key_exists(HostedPaymentPage::HOSTED_PAYMENT_PAGE_SESSION_ID_KEY, $queryParameters)) {
            /** @var string $hppSessionId */
            $hppSessionId = $queryParameters[HostedPaymentPage::HOSTED_PAYMENT_PAGE_SESSION_ID_KEY];

            if ($hostedPaymentPageSession->getSessionId() !== $hppSessionId) {
                throw new InvalidArgumentException('HPP Session id does not match. Please, check if it is a malicious attempt to mark an order as completed!');
            }
        } else {
            $this->logger->notice('The success url from Klarna does not contain the "sid" query parameter. The current request will continue, but pay attention! It could be dangerous not adding it!');
        }

        /** Order has been already created */
        if (array_key_exists(HostedPaymentPage::ORDER_ID_KEY, $queryParameters)) {
            /** @var string $orderId */
            $orderId = $queryParameters[HostedPaymentPage::ORDER_ID_KEY];

            $readOrder = new ReadOrder($orderId);
            $this->gateway->execute($readOrder);
            $orderDetails = $readOrder->getOrderDetails();
            Assert::isInstanceOf($orderDetails, OrderDetails::class);

            $payment->setDetails(PaymentDetailsHelper::storeOrderOnPaymentDetails($paymentDetails, $orderDetails));

            return;
        }
        /** Order should be created */
        if (!array_key_exists(HostedPaymentPage::AUTHORIZATION_TOKEN_KEY, $queryParameters)) {
            throw new RuntimeException('This point should not be reached. Both authorization token and order id could not exists on the same request!');
        }
        /** @var string $authorizationToken */
        $authorizationToken = $queryParameters[HostedPaymentPage::AUTHORIZATION_TOKEN_KEY];

        $convertSyliusPaymentToKlarnaOrder = new ConvertSyliusPaymentToKlarnaOrder($payment);
        $this->gateway->execute($convertSyliusPaymentToKlarnaOrder);
        $klarnaOrder = $convertSyliusPaymentToKlarnaOrder->getKlarnaOrder();
        Assert::isInstanceOf($klarnaOrder, Order::class);

        $createOrder = new CreateOrder($klarnaOrder, $authorizationToken);
        $this->gateway->execute($createOrder);
        $orderResponse = $createOrder->getOrderResponse();
        Assert::isInstanceOf($orderResponse, OrderResponse::class);

        $readOrder = new ReadOrder($orderResponse->getOrderId());
        $this->gateway->execute($readOrder);
        $orderDetails = $readOrder->getOrderDetails();
        Assert::isInstanceOf($orderDetails, OrderDetails::class);

        $payment->setDetails(PaymentDetailsHelper::storeOrderOnPaymentDetails($paymentDetails, $orderDetails));
    }
}
