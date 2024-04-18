<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\Enum;

enum Intent
{
    case buy;
    case tokenize;
    case buy_and_tokenize;
}
