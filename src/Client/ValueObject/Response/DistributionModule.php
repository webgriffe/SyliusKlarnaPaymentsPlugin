<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Response;

final readonly class DistributionModule
{
    public function __construct(
        private string $token,
        private string $standaloneUrl,
        private string $generationUrl,
    ) {
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getStandaloneUrl(): string
    {
        return $this->standaloneUrl;
    }

    public function getGenerationUrl(): string
    {
        return $this->generationUrl;
    }
}
