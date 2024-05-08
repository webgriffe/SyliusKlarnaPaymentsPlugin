<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Request\Api;

use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\HostedPaymentPage;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\HostedPaymentPageSession;

final class CreateHostedPaymentPageSession
{
    private ?HostedPaymentPageSession $hostedPaymentPageSession = null;

    public function __construct(
        private readonly HostedPaymentPage $hostedPaymentPage,
    ) {
    }

    public function getHostedPaymentPage(): HostedPaymentPage
    {
        return $this->hostedPaymentPage;
    }

    public function getHostedPaymentPageSession(): ?HostedPaymentPageSession
    {
        return $this->hostedPaymentPageSession;
    }

    public function setHostedPaymentPageSession(HostedPaymentPageSession $hostedPaymentPageSession): void
    {
        $this->hostedPaymentPageSession = $hostedPaymentPageSession;
    }
}
