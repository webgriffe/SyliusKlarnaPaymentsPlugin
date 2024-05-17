<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

use JsonSerializable;

final readonly class OrderLine implements JsonSerializable
{
    public function __construct(
        private string $name,
        private int $quantity,
        private int $taxRate,
        private Amount $totalAmount,
        private Amount $totalDiscountAmount,
        private Amount $totalTaxAmount,
        private Amount $unitPrice,
        private ?string $productUrl = null,
        private ?string $imageUrl = null,
        private ?string $merchantData = null,
        private ?string $quantityUnit = null,
        private ?string $reference = null,
        private ?string $type = null,
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

    public function getTaxRate(): int
    {
        return $this->taxRate;
    }

    public function getTotalAmount(): Amount
    {
        return $this->totalAmount;
    }

    public function getTotalDiscountAmount(): Amount
    {
        return $this->totalDiscountAmount;
    }

    public function getTotalTaxAmount(): Amount
    {
        return $this->totalTaxAmount;
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

    public function getQuantityUnit(): ?string
    {
        return $this->quantityUnit;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'name' => $this->getName(),
            'quantity' => $this->getQuantity(),
            'tax_rate' => $this->getTaxRate(),
            'total_amount' => $this->getTotalAmount()->getISO4217Amount(),
            'total_discount_amount' => $this->getTotalDiscountAmount()->getISO4217Amount(),
            'total_tax_amount' => $this->getTotalTaxAmount()->getISO4217Amount(),
            'unit_price' => $this->getUnitPrice()->getISO4217Amount(),
            'product_url' => $this->getProductUrl(),
            'image_url' => $this->getImageUrl(),
            'merchant_data' => $this->getMerchantData(),
            'quantity_unit' => $this->getQuantityUnit(),
            'type' => $this->getType(),
        ], static fn ($value) => $value !== null);
    }
}
