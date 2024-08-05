<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Response;

use DateTimeImmutable;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\HostedPaymentPageSessionStatus;

final readonly class HostedPaymentPageSessionDetails
{
    public function __construct(
        private string $authorizationToken,
        private DateTimeImmutable $expiresAt,
        private string $klarnaReference,
        private string $orderId,
        private string $sessionId,
        private HostedPaymentPageSessionStatus $status,
        private DateTimeImmutable $updatedAt,
    ) {
    }

    public function getAuthorizationToken(): string
    {
        return $this->authorizationToken;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getKlarnaReference(): string
    {
        return $this->klarnaReference;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getStatus(): HostedPaymentPageSessionStatus
    {
        return $this->status;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
