<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Converter;

use Sylius\Component\Core\Model\PaymentInterface;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Payment;

interface PaymentConverterInterface
{
    public function convert(
        PaymentInterface $payment,
        ?string $confirmationUrl,
        ?string $notificationUrl,
        ?string $pushUrl,
        ?string $authorizationUrl,
    ): Payment;
}
