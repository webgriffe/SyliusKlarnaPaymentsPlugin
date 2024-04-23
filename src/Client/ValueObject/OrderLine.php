<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

use JsonSerializable;

final readonly class OrderLine implements JsonSerializable
{
    public function __construct(
        private string $name,
        private int $quantity,
        private int $totalAmount,
        private int $unitPrice,
        private ?string $productUrl = null,
        private ?string $imageUrl = null,
        private ?string $merchantData = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getTotalAmount(): int
    {
        return $this->totalAmount;
    }

    public function getUnitPrice(): int
    {
        return $this->unitPrice;
    }

    public function getProductUrl(): ?string
    {
        return $this->productUrl;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function getMerchantData(): ?string
    {
        return $this->merchantData;
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'name' => $this->getName(),
            'quantity' => $this->getQuantity(),
            'total_amount' => $this->getTotalAmount(),
            'unit_price' => $this->getUnitPrice(),
            'product_url' => $this->getProductUrl(),
            'image_url' => $this->getImageUrl(),
            'merchant_data' => $this->getMerchantData(),
        ], static fn ($value) => $value !== null);
    }
}
