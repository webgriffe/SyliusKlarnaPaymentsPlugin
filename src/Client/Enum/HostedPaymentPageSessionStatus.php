<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum;

enum HostedPaymentPageSessionStatus: string
{
    case Waiting = 'WAITING';
    case Back = 'BACK';
    case InProgress = 'IN_PROGRESS';
    case ManualIdCheck = 'MANUAL_ID_CHECK';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELLED';
    case Failed = 'FAILED';
    case Disabled = 'DISABLED';
    case Error = 'ERROR';
}
