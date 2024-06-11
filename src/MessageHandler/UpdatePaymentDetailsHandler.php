<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\MessageHandler;

use Webgriffe\SyliusKlarnaPlugin\Message\UpdatePaymentDetails;

final class UpdatePaymentDetailsHandler
{
    public function __construct()
    {
    }

    public function __invoke(UpdatePaymentDetails $message): void
    {
    }
}
