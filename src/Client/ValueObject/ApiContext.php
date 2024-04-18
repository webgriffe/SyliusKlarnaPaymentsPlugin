<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

use Webgriffe\SyliusKlarnaPlugin\Client\Enum\ServerRegion;

final readonly class ApiContext
{
    public function __construct(
        private Authorization $authorization,
        private ServerRegion $region = ServerRegion::Europe,
        private bool $playground = true,
    ) {
    }

    public function getAuthorization(): Authorization
    {
        return $this->authorization;
    }

    public function getRegion(): ServerRegion
    {
        return $this->region;
    }

    public function isPlayground(): bool
    {
        return $this->playground;
    }
}
