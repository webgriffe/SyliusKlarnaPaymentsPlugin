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
        private readonly string $backUrl,
        private readonly string $cancelUrl,
        private readonly string $errorUrl,
        private readonly string $failureUrl,
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

    public function getBackUrl(): string
    {
        return $this->backUrl;
    }

    public function getCancelUrl(): string
    {
        return $this->cancelUrl;
    }

    public function getErrorUrl(): string
    {
        return $this->errorUrl;
    }

    public function getFailureUrl(): string
    {
        return $this->failureUrl;
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
