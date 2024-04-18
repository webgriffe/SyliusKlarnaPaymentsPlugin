<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum;

use Webgriffe\SyliusKlarnaPlugin\Client\Enum\ServerRegion;

final class KlarnaPaymentsApi
{
    public const CODE = 'klarna_payments';

    /**
     * @param array{sandbox: bool, username: string, password: string, server_region: string} $config
     */
    public function __construct(private readonly array $config)
    {
    }

    public function getUsername(): string
    {
        return $this->config['username'];
    }

    public function getPassword(): string
    {
        return $this->config['password'];
    }

    public function isSandBox(): bool
    {
        return $this->config['sandbox'];
    }

    public function getServerRegion(): ServerRegion
    {
        return ServerRegion::from($this->config['server_region']);
    }
}
