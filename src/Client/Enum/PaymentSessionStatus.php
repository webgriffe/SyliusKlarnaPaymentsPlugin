<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\Enum;

enum PaymentSessionStatus: string
{
    case Complete = 'complete';
    case Incomplete = 'incomplete';
}
