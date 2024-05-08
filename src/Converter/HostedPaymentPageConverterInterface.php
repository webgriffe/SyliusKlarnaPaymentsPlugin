<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Converter;

use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\HostedPaymentPage;

interface HostedPaymentPageConverterInterface
{
    public function convert(
        string $confirmationUrl,
        string $notificationUrl,
        string $cancelUrl,
        string $paymentSessionUrl,
    ): HostedPaymentPage;
}
