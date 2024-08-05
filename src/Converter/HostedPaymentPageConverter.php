<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Converter;

use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\PlaceOrderMode;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\PurchaseType;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\HostedPaymentPage;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\HostedPaymentPage\MerchantUrls;

final class HostedPaymentPageConverter implements HostedPaymentPageConverterInterface
{
    public function convert(
        string $confirmationUrl,
        string $notificationUrl,
        string $backUrl,
        string $cancelUrl,
        string $errorUrl,
        string $failureUrl,
        string $paymentSessionUrl,
    ): HostedPaymentPage {
        return new HostedPaymentPage(
            new MerchantUrls(
                $cancelUrl,
                $cancelUrl,
                $cancelUrl,
                $cancelUrl,
                $notificationUrl,
                $confirmationUrl,
            ),
            $paymentSessionUrl,
            [
                HostedPaymentPage::PLACE_ORDER_MODE_KEY => PlaceOrderMode::CaptureOrder,
                HostedPaymentPage::PURCHASE_TYPE_KEY => PurchaseType::Buy,
            ],
            null,
        );
    }
}
