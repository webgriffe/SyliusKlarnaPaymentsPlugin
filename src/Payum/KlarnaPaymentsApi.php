<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum;

final class KlarnaPaymentsApi
{
    public const CODE = 'klarna_payments';

    /**
     * @param array{sandbox: bool, username: string, password: string} $config
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
}
