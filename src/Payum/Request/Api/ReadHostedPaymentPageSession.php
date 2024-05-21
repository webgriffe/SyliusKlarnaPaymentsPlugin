<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Request\Api;

use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\HostedPaymentPageSessionDetails;

final class ReadHostedPaymentPageSession
{
    private ?HostedPaymentPageSessionDetails $hostedPaymentPageSessionDetails = null;

    public function __construct(
        private readonly string $sessionId,
    ) {
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getHostedPaymentPageSessionDetails(): ?HostedPaymentPageSessionDetails
    {
        return $this->hostedPaymentPageSessionDetails;
    }

    public function setHostedPaymentPageSessionDetails(
        HostedPaymentPageSessionDetails $hostedPaymentPageSessionDetails,
    ): void {
        $this->hostedPaymentPageSessionDetails = $hostedPaymentPageSessionDetails;
    }
}
