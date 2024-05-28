<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response;

final readonly class OrderDetails
{
    public function __construct(
        private string $orderId,
        private string $fraudStatus,
    ) {
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getFraudStatus(): string
    {
        return $this->fraudStatus;
    }
}
