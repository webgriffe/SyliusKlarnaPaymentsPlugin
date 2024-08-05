<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum;

enum PaymentSessionStatus: string
{
    case Complete = 'complete';
    case Incomplete = 'incomplete';
}
