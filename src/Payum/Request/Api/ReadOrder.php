<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Request\Api;

use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Response\OrderDetails;

final class ReadOrder
{
    private ?OrderDetails $orderDetails = null;

    public function __construct(
        private readonly string $orderId,
    ) {
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getOrderDetails(): ?OrderDetails
    {
        return $this->orderDetails;
    }

    public function setOrderDetails(OrderDetails $orderDetails): void
    {
        $this->orderDetails = $orderDetails;
    }
}
