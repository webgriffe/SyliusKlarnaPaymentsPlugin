<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

use Stringable;

final readonly class Authorization implements Stringable
{
    public function __construct(
        private string $username,
        private string $password,
    ) {
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getHeader(): string
    {
        return base64_encode($this->username . ':' . $this->password);
    }

    public function __toString(): string
    {
        return $this->getHeader();
    }
}
