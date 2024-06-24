<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

use JsonSerializable;

final readonly class ProductIdentifiers implements JsonSerializable
{
    public function __construct(
        private ?string $brand = null,
        private ?string $categoryPath = null,
        private ?string $globalTradeItemNumber = null,
        private ?string $manufacturerPartNumber = null,
        private ?string $color = null,
        private ?string $size = null,
    ) {
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function getCategoryPath(): ?string
    {
        return $this->categoryPath;
    }

    public function getGlobalTradeItemNumber(): ?string
    {
        return $this->globalTradeItemNumber;
    }

    public function getManufacturerPartNumber(): ?string
    {
        return $this->manufacturerPartNumber;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function jsonSerialize(): array
    {
        $payload = [
            'brand' => $this->getBrand(),
            'category_path' => $this->getCategoryPath(),
            'global_trade_item_number' => $this->getGlobalTradeItemNumber(),
            'manufacturer_part_number' => $this->getManufacturerPartNumber(),
            'color' => $this->getColor(),
            'size' => $this->getSize(),
        ];

        return array_filter($payload, static fn ($value) => $value !== null);
    }
}
