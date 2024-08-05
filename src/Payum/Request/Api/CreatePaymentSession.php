<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Request\Api;

use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Payment;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Response\PaymentSession;

final class CreatePaymentSession
{
    private ?PaymentSession $paymentSession = null;

    public function __construct(
        private readonly Payment $payment,
    ) {
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }

    public function getPaymentSession(): ?PaymentSession
    {
        return $this->paymentSession;
    }

    public function setPaymentSession(?PaymentSession $paymentSession): void
    {
        $this->paymentSession = $paymentSession;
    }
}
