<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client;

use Webgriffe\SyliusKlarnaPlugin\Client\Exception\ClientException;
use Webgriffe\SyliusKlarnaPlugin\Client\Exception\SessionCreateFailedException;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\ApiContext;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payment;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSession;

interface ClientInterface
{
    /**
     * @docs https://docs.klarna.com/klarna-payments/integrate-with-klarna-payments/step-1-initiate-a-payment/
     *
     * @throws ClientException
     * @throws SessionCreateFailedException
     */
    public function createASession(
        ApiContext $apiContext,
        Payment $payment,
    ): PaymentSession;
}
