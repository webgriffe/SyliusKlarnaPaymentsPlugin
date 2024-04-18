<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client;

enum PaymentState: string
{
    public const SUCCESS = 'success';

    public const PENDING = 'pending';

    public const AWAITING_CONFIRMATION = 'awaiting_confirmation';

    public const CANCELLED = 'cancelled';
}
