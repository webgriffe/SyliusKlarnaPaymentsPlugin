<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

use JsonSerializable;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\OrderLineType;

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
        private ?OrderLineType $type = null,
        private ?ProductIdentifiers $productIdentifiers = null,
        private ?Subscription $subscription = null,
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

    public function getType(): ?OrderLineType
    {
        return $this->type;
    }

    public function getProductIdentifiers(): ?ProductIdentifiers
    {
        return $this->productIdentifiers;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'image_url' => $this->getImageUrl(),
            'merchant_data' => $this->getMerchantData(),
            'name' => $this->getName(),
            'product_identifiers' => $this->getProductIdentifiers(),
            'product_url' => $this->getProductUrl(),
            'quantity' => $this->getQuantity(),
            'quantity_unit' => $this->getQuantityUnit(),
            'reference' => $this->getReference(),
            'tax_rate' => $this->getTaxRate(),
            'total_amount' => $this->getTotalAmount()->getISO4217Amount(),
            'total_discount_amount' => $this->getTotalDiscountAmount()->getISO4217Amount(),
            'total_tax_amount' => $this->getTotalTaxAmount()->getISO4217Amount(),
            'type' => $this->getType()->value,
            'unit_price' => $this->getUnitPrice()->getISO4217Amount(),
            'subscription' => $this->getSubscription(),
        ], static fn ($value) => $value !== null);
    }
}
