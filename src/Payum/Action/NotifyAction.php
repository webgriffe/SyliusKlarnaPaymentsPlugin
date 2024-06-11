<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Webgriffe\SyliusKlarnaPlugin\Model\PaymentDetails;
use Webmozart\Assert\Assert;

/**
 * @psalm-import-type StoredPaymentDetails from PaymentDetails
 */
final class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

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

        // This is needed to populate the http request with GET and POST params from current request
        $this->gateway->execute($httpRequest = new GetHttpRequest());

        /** @var array{token: string, status: string} $requestParameters */
        $requestParameters = $httpRequest->request;

        dd($requestParameters);
    }

    public function supports($request): bool
    {
        return $request instanceof Notify &&
            $request->getModel() instanceof SyliusPaymentInterface
        ;
    }
}
