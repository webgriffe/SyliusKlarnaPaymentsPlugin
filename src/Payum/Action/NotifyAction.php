<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\HostedPaymentPageSessionStatus;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSessionDetails;
use Webgriffe\SyliusKlarnaPlugin\Helper\PaymentDetailsHelper;
use Webgriffe\SyliusKlarnaPlugin\Model\PaymentDetails;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\Api\ReadPaymentSession;
use Webmozart\Assert\Assert;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-suppress TypeDoesNotContainType
 *
 * @psalm-import-type StoredPaymentDetails from PaymentDetails
 */
final class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param Notify|mixed $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        Assert::isInstanceOf($request, Notify::class);

        /** @var SyliusPaymentInterface|mixed $payment */
        $payment = $request->getModel();
        Assert::isInstanceOf($payment, SyliusPaymentInterface::class);

        /** @var string|int $paymentId */
        $paymentId = $payment->getId();
        $this->logger->info(sprintf(
            'Start notify action for Sylius payment with ID "%s".',
            $paymentId,
        ));

        // This is needed to populate the http request with GET and POST params from current request
        $this->gateway->execute($httpRequest = new GetHttpRequest());

        /** @var array{event_id: string, session: array{session_id: string, status: string, updated_at: string, expires_at: string, order_id?: string, klarna_reference?: string}} $requestParameters */
        $requestParameters = $httpRequest->request;

        $this->logger->info(sprintf(
            'Received Klarna notification for payment with ID "%s".',
            $paymentId,
        ), ['Request parameters' => $requestParameters]);

        $storedPaymentDetails = $payment->getDetails();
        PaymentDetailsHelper::assertStoredPaymentDetailsAreValid($storedPaymentDetails);

        $paymentDetails = PaymentDetails::createFromStoredPaymentDetails($storedPaymentDetails);

        $oldHostedPaymentPage = $paymentDetails->getHostedPaymentPageStatus();
        $paymentDetails->setHostedPaymentPageStatus(HostedPaymentPageSessionStatus::tryFrom($requestParameters['session']['status']));
        $paymentDetails->setOrderId($requestParameters['session']['order_id'] ?? null);
        $paymentDetails->setKlarnaReference($requestParameters['session']['klarna_reference'] ?? null);

        if ($oldHostedPaymentPage !== $paymentDetails->getHostedPaymentPageStatus()) {
            $readPaymentSession = new ReadPaymentSession($paymentDetails->getPaymentSessionId());
            $this->gateway->execute($readPaymentSession);

            $paymentSessionDetails = $readPaymentSession->getPaymentSessionDetails();
            Assert::isInstanceOf($paymentSessionDetails, PaymentSessionDetails::class);

            $paymentDetails->setPaymentSessionStatus($paymentSessionDetails->getStatus());
        }

        $payment->setDetails($paymentDetails->toStoredPaymentDetails());
    }

    public function supports($request): bool
    {
        return $request instanceof Notify &&
            $request->getModel() instanceof SyliusPaymentInterface
        ;
    }
}
