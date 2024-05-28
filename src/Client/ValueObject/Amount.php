<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

final readonly class Amount
{
    private function __construct(
        private int $syliusAmount,
    ) {
    }

    public function getSyliusAmount(): int
    {
        return $this->syliusAmount;
    }

    public function getISO4217Amount(): int
    {
        return $this->syliusAmount;
    }

    public static function fromSyliusAmount(int $syliusAmount): self
    {
        return new self($syliusAmount);
    }
}
