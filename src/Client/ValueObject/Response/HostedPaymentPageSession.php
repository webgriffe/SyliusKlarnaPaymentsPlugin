<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response;

use DateTimeInterface;

final readonly class HostedPaymentPageSession
{
    public function __construct(
        private string $sessionId,
        private string $redirectUrl,
        private string $sessionUrl,
        private string $qrCodeUrl,
        private string $distributionUrl,
        private DateTimeInterface $expiresAt,
        private DistributionModule $distributionModule,
    ) {
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    public function getSessionUrl(): string
    {
        return $this->sessionUrl;
    }

    public function getQrCodeUrl(): string
    {
        return $this->qrCodeUrl;
    }

    public function getDistributionUrl(): string
    {
        return $this->distributionUrl;
    }

    public function getExpiresAt(): DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function getDistributionModule(): DistributionModule
    {
        return $this->distributionModule;
    }
}
