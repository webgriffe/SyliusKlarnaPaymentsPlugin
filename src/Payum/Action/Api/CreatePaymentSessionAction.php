<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Webgriffe\SyliusKlarnaPlugin\Client\ClientInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\ApiContext;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Authorization;
use Webgriffe\SyliusKlarnaPlugin\Payum\KlarnaPaymentsApi;
use Webgriffe\SyliusKlarnaPlugin\Payum\Request\Api\CreatePaymentSession;
use Webmozart\Assert\Assert;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class CreatePaymentSessionAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct(
        private readonly ClientInterface $client,
    ) {
        $this->apiClass = KlarnaPaymentsApi::class;
    }

    /**
     * @param CreatePaymentSession|mixed $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        Assert::isInstanceOf($request, CreatePaymentSession::class);

        $klarnaPaymentsApi = $this->api;
        Assert::isInstanceOf($klarnaPaymentsApi, KlarnaPaymentsApi::class);
        $apiContext = new ApiContext(
            new Authorization($klarnaPaymentsApi->getUsername(), $klarnaPaymentsApi->getPassword()),
            $klarnaPaymentsApi->getServerRegion(),
            $klarnaPaymentsApi->isSandBox(),
        );
        $paymentSession = $this->client->createPaymentSession($apiContext, $request->getPayment());

        $request->setPaymentSession($paymentSession);
    }

    public function supports($request): bool
    {
        return $request instanceof CreatePaymentSession;
    }
}
