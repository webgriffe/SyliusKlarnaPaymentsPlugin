<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Request;

use Sylius\Component\Core\Model\PaymentInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payment;

final class ConvertSyliusPaymentToKlarnaPayment
{
    private ?Payment $klarnaPayment = null;

    public function __construct(
        private readonly PaymentInterface $syliusPayment,
        private readonly string $confirmationUrl,
        private readonly string $notificationUrl,
    ) {
    }

    public function getSyliusPayment(): PaymentInterface
    {
        return $this->syliusPayment;
    }

    public function getConfirmationUrl(): string
    {
        return $this->confirmationUrl;
    }

    public function getNotificationUrl(): string
    {
        return $this->notificationUrl;
    }

    public function setKlarnaPayment(Payment $klarnaPayment): void
    {
        $this->klarnaPayment = $klarnaPayment;
    }

    public function getKlarnaPayment(): ?Payment
    {
        return $this->klarnaPayment;
    }
}
