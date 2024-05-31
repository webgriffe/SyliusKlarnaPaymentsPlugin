<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\Enum;

enum OrderStatus: string
{
    case Authorized = 'AUTHORIZED';
    case PartCaptured = 'PART_CAPTURED';
    case Captured = 'CAPTURED';
    case Cancelled = 'CANCELLED';
    case Expired = 'EXPIRED';
    case Closed = 'CLOSED';
}
