<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response;

use DateTimeImmutable;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\AcquiringChannel;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\Intent;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\Status;

final readonly class PaymentSessionDetails
{
    public function __construct(
        private AcquiringChannel $acquiringChannel,
        private string $authorizationToken,
        private string $clientToken,
        private DateTimeImmutable $expiresAt,
        private Status $status,
        private Intent $intent,
    ) {
    }

    public function getAcquiringChannel(): AcquiringChannel
    {
        return $this->acquiringChannel;
    }

    public function getAuthorizationToken(): string
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

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getIntent(): Intent
    {
        return $this->intent;
    }
}
