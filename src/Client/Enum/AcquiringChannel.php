<?php
declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\Enum;

enum AcquiringChannel
{
    case ECOMMERCE;
    case IN_STORE;
    case TELESALES;
}
