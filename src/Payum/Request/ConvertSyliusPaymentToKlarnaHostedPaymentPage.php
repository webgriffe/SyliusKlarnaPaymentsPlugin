<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Request;

use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\HostedPaymentPage;

final class ConvertSyliusPaymentToKlarnaHostedPaymentPage
{
    private ?HostedPaymentPage $klarnaHostedPaymentPage = null;

    public function __construct(
        private readonly string $confirmationUrl,
        private readonly string $notificationUrl,
        private readonly string $cancelUrl,
        private readonly string $paymentSessionUrl,
    ) {
    }

    public function getConfirmationUrl(): string
    {
        return $this->confirmationUrl;
    }

    public function getNotificationUrl(): string
    {
        return $this->notificationUrl;
    }

    public function getCancelUrl(): string
    {
        return $this->cancelUrl;
    }

    public function getPaymentSessionUrl(): string
    {
        return $this->paymentSessionUrl;
    }

    public function getKlarnaHostedPaymentPage(): ?HostedPaymentPage
    {
        return $this->klarnaHostedPaymentPage;
    }

    public function setKlarnaHostedPaymentPage(HostedPaymentPage $klarnaHostedPaymentPage): void
    {
        $this->klarnaHostedPaymentPage = $klarnaHostedPaymentPage;
    }
}
