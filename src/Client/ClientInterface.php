<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Client;

use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Exception\ClientException;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Exception\HostedPaymentPageSessionCreateFailedException;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Exception\HostedPaymentPageSessionReadFailedException;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Exception\OrderCreateFailedException;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Exception\OrderReadFailedException;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Exception\PaymentSessionCreateFailedException;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Exception\PaymentSessionReadFailedException;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\ApiContext;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\HostedPaymentPage;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Order;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Payment;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Response\HostedPaymentPageSession;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Response\HostedPaymentPageSessionDetails;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Response\Order as OrderResponse;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Response\OrderDetails;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Response\PaymentSession;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Response\PaymentSessionDetails;

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

    /**
     * @docs https://docs.klarna.com/api/ordermanagement/#operation/getOrder
     *
     * @throws ClientException
     * @throws OrderReadFailedException
     */
    public function getOrderDetails(
        ApiContext $apiContext,
        string $orderId,
    ): OrderDetails;

    public function createPaymentSessionUrl(
        ApiContext $apiContext,
        string $sessionId,
    ): string;
}
