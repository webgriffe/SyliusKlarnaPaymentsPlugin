<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Converter;

use Sylius\Component\Core\Model\PaymentInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payment;

interface PaymentConverterInterface
{
    public function convert(
        PaymentInterface $payment,
    ): Payment;
}
