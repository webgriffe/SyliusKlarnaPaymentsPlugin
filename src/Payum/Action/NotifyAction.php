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
use Symfony\Component\Messenger\MessageBusInterface;
use Webgriffe\SyliusKlarnaPlugin\Message\UpdatePaymentDetails;
use Webgriffe\SyliusKlarnaPlugin\Model\PaymentDetails;
use Webmozart\Assert\Assert;

/**
 * @psalm-import-type StoredPaymentDetails from PaymentDetails
 */
final class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly MessageBusInterface $messageBus,
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

        $this->logger->info(sprintf(
            'Start notify action for Sylius payment with ID "%s".',
            $payment->getId(),
        ));

        // This is needed to populate the http request with GET and POST params from current request
        $this->gateway->execute($httpRequest = new GetHttpRequest());

        /** @var array{event_id: string, session: array{session_id: string, status: string, updated_at: string, expires_at: string, order_id?: string, klarna_reference?: string}} $requestParameters */
        $requestParameters = $httpRequest->request;

        $this->logger->info(sprintf(
            'Received Klarna notification for payment with ID "%s".',
            $payment->getId(),
        ), ['Request parameters' => $requestParameters]);

        // @TODO: dispatch only when status === COMPLETED?
        $this->messageBus->dispatch(new UpdatePaymentDetails($payment->getId()));
    }

    public function supports($request): bool
    {
        return $request instanceof Notify &&
            $request->getModel() instanceof SyliusPaymentInterface
        ;
    }
}
