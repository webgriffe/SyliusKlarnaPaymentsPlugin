<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum;

enum Intent: string
{
    case buy = 'buy';
    case tokenize = 'tokenize';
    case buy_and_tokenize = 'buy_and_tokenize';
}
