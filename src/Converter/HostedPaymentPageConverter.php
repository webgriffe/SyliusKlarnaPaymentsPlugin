<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Converter;

use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\HostedPaymentPage;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\HostedPaymentPage\MerchantUrls;

final class HostedPaymentPageConverter implements HostedPaymentPageConverterInterface
{
    public function convert(
        string $confirmationUrl,
        string $notificationUrl,
        string $cancelUrl,
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
            null,
            null,
        );
    }
}
