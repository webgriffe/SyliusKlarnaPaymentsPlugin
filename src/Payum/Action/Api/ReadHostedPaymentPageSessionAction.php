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
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Request\Api\ReadHostedPaymentPageSession;
use Webmozart\Assert\Assert;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ReadHostedPaymentPageSessionAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct(
        private readonly ClientInterface $client,
    ) {
        $this->apiClass = KlarnaPaymentsApi::class;
    }

    /**
     * @param ReadHostedPaymentPageSession|mixed $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        Assert::isInstanceOf($request, ReadHostedPaymentPageSession::class);

        $klarnaPaymentsApi = $this->api;
        Assert::isInstanceOf($klarnaPaymentsApi, KlarnaPaymentsApi::class);
        $apiContext = new ApiContext(
            new Authorization($klarnaPaymentsApi->getUsername(), $klarnaPaymentsApi->getPassword()),
            $klarnaPaymentsApi->getServerRegion(),
            $klarnaPaymentsApi->isSandBox(),
        );
        $hostedPaymentPageSessionDetails = $this->client->getHostedPaymentPageSessionDetails($apiContext, $request->getSessionId());

        $request->setHostedPaymentPageSessionDetails($hostedPaymentPageSessionDetails);
    }

    public function supports($request): bool
    {
        return $request instanceof ReadHostedPaymentPageSession;
    }
}
