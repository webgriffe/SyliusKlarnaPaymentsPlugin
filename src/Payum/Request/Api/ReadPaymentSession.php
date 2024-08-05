<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Request\Api;

use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Response\PaymentSessionDetails;

final class ReadPaymentSession
{
    private ?PaymentSessionDetails $paymentSessionDetails = null;

    public function __construct(
        private readonly string $sessionId,
    ) {
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getPaymentSessionDetails(): ?PaymentSessionDetails
    {
        return $this->paymentSessionDetails;
    }

    public function setPaymentSessionDetails(?PaymentSessionDetails $paymentSessionDetails): void
    {
        $this->paymentSessionDetails = $paymentSessionDetails;
    }
}
