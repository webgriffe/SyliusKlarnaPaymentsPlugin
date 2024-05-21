<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client;

use Webgriffe\SyliusKlarnaPlugin\Client\Exception\ClientException;
use Webgriffe\SyliusKlarnaPlugin\Client\Exception\HostedPaymentPageSessionCreateFailedException;
use Webgriffe\SyliusKlarnaPlugin\Client\Exception\HostedPaymentPageSessionReadFailedException;
use Webgriffe\SyliusKlarnaPlugin\Client\Exception\OrderCreateFailedException;
use Webgriffe\SyliusKlarnaPlugin\Client\Exception\PaymentSessionCreateFailedException;
use Webgriffe\SyliusKlarnaPlugin\Client\Exception\PaymentSessionReadFailedException;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\ApiContext;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\HostedPaymentPage;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Order;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payment;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\HostedPaymentPageSession;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\HostedPaymentPageSessionDetails;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\Order as OrderResponse;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSession;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSessionDetails;

interface ClientInterface
{
    /**
     * @docs https://docs.klarna.com/api/payments/#operation/createCreditSession
     *
     * @throws ClientException
     * @throws PaymentSessionCreateFailedException
     */
    public function createPaymentSession(
        ApiContext $apiContext,
        Payment $payment,
    ): PaymentSession;

    /**
     * @docs https://docs.klarna.com/api/payments/#operation/readCreditSession
     *
     * @throws ClientException
     * @throws PaymentSessionReadFailedException
     */
    public function getPaymentSessionDetails(
        ApiContext $apiContext,
        string $sessionId,
    ): PaymentSessionDetails;

    /**
     * @docs https://docs.klarna.com/api/hpp-merchant/#operation/createHppSession
     *
     * @throws ClientException
     * @throws HostedPaymentPageSessionCreateFailedException
     */
    public function createHostedPaymentPageSession(
        ApiContext $apiContext,
        HostedPaymentPage $hostedPaymentPage,
    ): HostedPaymentPageSession;

    /**
     * @docs https://docs.klarna.com/api/hpp-merchant/#operation/getSessionById
     *
     * @throws ClientException
     * @throws HostedPaymentPageSessionReadFailedException
     */
    public function getHostedPaymentPageSessionDetails(
        ApiContext $apiContext,
        string $sessionId,
    ): HostedPaymentPageSessionDetails;

    /**
     * @docs https://docs.klarna.com/api/payments/#operation/createOrder
     *
     * @throws ClientException
     * @throws OrderCreateFailedException
     */
    public function createOrder(
        ApiContext $apiContext,
        Order $order,
        string $authorizationToken,
    ): OrderResponse;

    public function createPaymentSessionUrl(
        ApiContext $apiContext,
        string $sessionId,
    ): string;
}
