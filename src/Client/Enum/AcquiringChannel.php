<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\Enum;

enum AcquiringChannel: string
{
    case ECOMMERCE = 'ECOMMERCE';
    case IN_STORE = 'IN_STORE';
    case TELESALES = 'TELESALES';
}
