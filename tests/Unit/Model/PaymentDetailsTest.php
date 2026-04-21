<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusKlarnaPaymentsPlugin\Unit\Model;

use PHPUnit\Framework\TestCase;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\HostedPaymentPageSessionStatus;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\PaymentSessionStatus;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Model\PaymentDetails;

final class PaymentDetailsTest extends TestCase
{
    private function makeDetails(
        ?string $paymentStatus,
        ?string $hppStatus,
    ): PaymentDetails {
        return PaymentDetails::createFromStoredPaymentDetails([
            'payment_session_id' => 'session-id',
            'payment_client_token' => 'token',
            'payment_status' => $paymentStatus,
            'hosted_payment_page_session_id' => 'hpp-session-id',
            'hosted_payment_page_redirect_url' => 'https://pay.klarna.com',
            'hosted_payment_page_status' => $hppStatus,
            'order_id' => null,
            'order_status' => null,
            'klarna_reference' => null,
        ]);
    }

    // --- isCanceled ---

    public function testIsCanceledWhenHppIsBackAndSessionIsIncomplete(): void
    {
        $details = $this->makeDetails(PaymentSessionStatus::Incomplete->value, HostedPaymentPageSessionStatus::Back->value);

        self::assertTrue($details->isCanceled());
    }

    public function testIsCanceledWhenHppIsCancelledAndSessionIsIncomplete(): void
    {
        $details = $this->makeDetails(PaymentSessionStatus::Incomplete->value, HostedPaymentPageSessionStatus::Cancelled->value);

        self::assertTrue($details->isCanceled());
    }

    public function testIsNotCanceledWhenSessionIsCompleteEvenIfHppIsBack(): void
    {
        $details = $this->makeDetails(PaymentSessionStatus::Complete->value, HostedPaymentPageSessionStatus::Back->value);

        self::assertFalse($details->isCanceled());
    }

    public function testIsNotCanceledWhenSessionIsCompleteEvenIfHppIsCancelled(): void
    {
        $details = $this->makeDetails(PaymentSessionStatus::Complete->value, HostedPaymentPageSessionStatus::Cancelled->value);

        self::assertFalse($details->isCanceled());
    }

    // --- isSuccessfully ---

    public function testIsSuccessfullyWhenSessionIsCompleteAndHppIsCompleted(): void
    {
        $details = $this->makeDetails(PaymentSessionStatus::Complete->value, HostedPaymentPageSessionStatus::Completed->value);

        self::assertTrue($details->isSuccessfully());
    }

    public function testIsSuccessfullyWhenSessionIsCompleteAndHppIsBack(): void
    {
        $details = $this->makeDetails(PaymentSessionStatus::Complete->value, HostedPaymentPageSessionStatus::Back->value);

        self::assertTrue($details->isSuccessfully());
    }

    public function testIsNotSuccessfullyWhenSessionIsIncomplete(): void
    {
        $details = $this->makeDetails(PaymentSessionStatus::Incomplete->value, HostedPaymentPageSessionStatus::Completed->value);

        self::assertFalse($details->isSuccessfully());
    }

    public function testIsNotSuccessfullyWhenHppIsFailed(): void
    {
        $details = $this->makeDetails(PaymentSessionStatus::Complete->value, HostedPaymentPageSessionStatus::Failed->value);

        self::assertFalse($details->isSuccessfully());
    }

    public function testIsNotSuccessfullyWhenHppIsError(): void
    {
        $details = $this->makeDetails(PaymentSessionStatus::Complete->value, HostedPaymentPageSessionStatus::Error->value);

        self::assertFalse($details->isSuccessfully());
    }
}
