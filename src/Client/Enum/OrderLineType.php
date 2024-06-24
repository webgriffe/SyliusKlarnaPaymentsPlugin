<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\Enum;

enum OrderLineType: string
{
    case Physical = 'physical';
    case Discount = 'discount';
    case ShippingFee = 'shipping_fee';
    case SalesTax = 'sales_tax';
    case Digital = 'digital';
    case GiftCard = 'gift_card';
    case StoreCredit = 'store_credit';
    case Surcharge = 'surcharge';
}
