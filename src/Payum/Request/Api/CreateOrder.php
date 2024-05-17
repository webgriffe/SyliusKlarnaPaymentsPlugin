<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum\Request\Api;

use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Order;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response\Order as OrderResponse;

final class CreateOrder
{
    private ?OrderResponse $orderResponse = null;

    public function __construct(
        private readonly Order $order,
        private readonly string $authorizationToken,
    ) {
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getAuthorizationToken(): string
    {
        return $this->authorizationToken;
    }

    public function getOrderResponse(): ?OrderResponse
    {
        return $this->orderResponse;
    }

    public function setOrderResponse(OrderResponse $orderResponse): void
    {
        $this->orderResponse = $orderResponse;
    }
}
