<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

use JsonSerializable;

final readonly class Order implements JsonSerializable
{
    public function __construct(
    ) {
    }

    public function jsonSerialize(): array
    {
        return [];
    }
}
