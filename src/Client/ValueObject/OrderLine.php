<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

use JsonSerializable;

final readonly class OrderLine implements JsonSerializable
{
    public function __construct(
        private string $name,
        private int $quantity,
        private Amount $totalAmount,
        private Amount $unitPrice,
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

    public function getTotalAmount(): Amount
    {
        return $this->totalAmount;
    }

    public function getUnitPrice(): Amount
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
            'total_amount' => $this->getTotalAmount()->getISO4217Amount(),
            'unit_price' => $this->getUnitPrice()->getISO4217Amount(),
            'product_url' => $this->getProductUrl(),
            'image_url' => $this->getImageUrl(),
            'merchant_data' => $this->getMerchantData(),
        ], static fn ($value) => $value !== null);
    }
}
