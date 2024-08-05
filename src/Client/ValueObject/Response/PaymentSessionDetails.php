<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Response;

use DateTimeImmutable;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\AcquiringChannel;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\Intent;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\PaymentSessionStatus;

final readonly class PaymentSessionDetails
{
    public function __construct(
        private AcquiringChannel $acquiringChannel,
        private string $clientToken,
        private DateTimeImmutable $expiresAt,
        private PaymentSessionStatus $status,
        private Intent $intent,
        private ?string $authorizationToken = null,
    ) {
    }

    public function getAcquiringChannel(): AcquiringChannel
    {
        return $this->acquiringChannel;
    }

    public function getAuthorizationToken(): ?string
    {
        return $this->authorizationToken;
    }

    public function getClientToken(): string
    {
        return $this->clientToken;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getStatus(): PaymentSessionStatus
    {
        return $this->status;
    }

    public function getIntent(): Intent
    {
        return $this->intent;
    }
}
