<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusKlarnaPlugin\Service;

use DateTimeImmutable;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\ClientInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\AcquiringChannel;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\HostedPaymentPageSessionStatus;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\Intent;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\OrderStatus;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\PaymentSessionStatus;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\ApiContext;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\HostedPaymentPage;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Order;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payment;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\AuthorizedPaymentMethod;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\DistributionModule;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\HostedPaymentPageSession;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\HostedPaymentPageSessionDetails;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\Order as OrderResponse;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\OrderDetails;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSession;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\PaymentSessionDetails;

final readonly class DummyClient implements ClientInterface
{
    public const FAKE_TOKEN_ID = 'FAKE_TOKEN_ID';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function createPaymentSession(
        ApiContext $apiContext,
        Payment $payment,
    ): PaymentSession {
        return new PaymentSession(
            self::FAKE_TOKEN_ID,
            'FAKE_PAYMENT_SESSION_ID',
        );
    }

    public function getPaymentSessionDetails(
        ApiContext $apiContext,
        string $sessionId,
    ): PaymentSessionDetails {
        return new PaymentSessionDetails(
            AcquiringChannel::ECOMMERCE,
            self::FAKE_TOKEN_ID,
            new DateTimeImmutable('3 hours'),
            PaymentSessionStatus::Complete,
            Intent::buy,
        );
    }

    public function createHostedPaymentPageSession(
        ApiContext $apiContext,
        HostedPaymentPage $hostedPaymentPage,
    ): HostedPaymentPageSession {
        // Redirect to any other page. This is just a dummy implementation.
        $homepageUrl = $this->urlGenerator->generate('sylius_shop_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return new HostedPaymentPageSession(
            'FAKE_HPP_SESSION_ID',
            $homepageUrl,
            'https://example.com/session-url',
            'https://example.com/qr-code',
            'https://example.com/distribution-url',
            new DateTimeImmutable(),
            new DistributionModule(
                'TOKEN',
                'https://example.com/standaalone-url',
                'https://example.com/generation-url',
            ),
        );
    }

    public function getHostedPaymentPageSessionDetails(
        ApiContext $apiContext,
        string $sessionId,
    ): HostedPaymentPageSessionDetails {
        return new HostedPaymentPageSessionDetails(
            'AUTHORIZATION_TOKEN',
            new DateTimeImmutable('3 hours'),
            'KLARNA_REFERENCE',
            'ORDER_ID',
            'FAKE_HPP_SESSION_ID',
            HostedPaymentPageSessionStatus::Completed,
            new DateTimeImmutable(),
        );
    }

    public function createOrder(
        ApiContext $apiContext,
        Order $order,
        string $authorizationToken,
    ): OrderResponse {
        return new OrderResponse(
            'ORDER_ID',
            'KLARNA_REFERENCE',
            'green',
            new AuthorizedPaymentMethod(1, 1, 'type'),
        );
    }

    public function getOrderDetails(
        ApiContext $apiContext,
        string $orderId,
    ): OrderDetails {
        return new OrderDetails(
            'ORDER_ID',
            'KLARNA_REFERENCE',
            OrderStatus::Captured,
        );
    }

    public function createPaymentSessionUrl(
        ApiContext $apiContext,
        string $sessionId,
    ): string {
        return 'https://dummy-klarna-client.com/payments/v1/sessions/' . $sessionId;
    }

}
