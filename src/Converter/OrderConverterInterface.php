<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Converter;

use Sylius\Component\Core\Model\PaymentInterface;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Order;

interface OrderConverterInterface
{
    public function convert(
        PaymentInterface $payment,
    ): Order;
}
