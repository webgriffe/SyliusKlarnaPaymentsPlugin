<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum;

enum PurchaseType: string
{
    case Buy = 'BUY';
    case Rent = 'RENT';
    case Book = 'BOOK';
    case Subscribe = 'SUBSCRIBE';
    case Download = 'DOWNLOAD';
    case Order = 'ORDER';
    case Continue = 'CONTINUE';
}
