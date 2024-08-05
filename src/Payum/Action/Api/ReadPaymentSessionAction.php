<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ClientInterface;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\ApiContext;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Authorization;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApi;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Request\Api\ReadPaymentSession;
use Webmozart\Assert\Assert;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ReadPaymentSessionAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct(
        private readonly ClientInterface $client,
    ) {
        $this->apiClass = KlarnaPaymentsApi::class;
    }

    /**
     * @param ReadPaymentSession|mixed $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        Assert::isInstanceOf($request, ReadPaymentSession::class);

        $klarnaPaymentsApi = $this->api;
        Assert::isInstanceOf($klarnaPaymentsApi, KlarnaPaymentsApi::class);
        $apiContext = new ApiContext(
            new Authorization($klarnaPaymentsApi->getUsername(), $klarnaPaymentsApi->getPassword()),
            $klarnaPaymentsApi->getServerRegion(),
            $klarnaPaymentsApi->isSandBox(),
        );
        $paymentSessionDetails = $this->client->getPaymentSessionDetails($apiContext, $request->getSessionId());

        $request->setPaymentSessionDetails($paymentSessionDetails);
    }

    public function supports($request): bool
    {
        return $request instanceof ReadPaymentSession;
    }
}
