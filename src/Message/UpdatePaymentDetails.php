<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Message;

final readonly class UpdatePaymentDetails
{
    public function __construct(
        private string|int $paymentId,
    ) {
    }

    public function getPaymentId(): int|string
    {
        return $this->paymentId;
    }
}
