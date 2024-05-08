<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

use JsonSerializable;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\HostedPaymentPage\MerchantUrls;

final readonly class HostedPaymentPage implements JsonSerializable
{
    public function __construct(
        private MerchantUrls $merchantUrls,
        private array $options,
        private string $paymentSessionUrl,
        private ?string $profileId = null,
    ) {
    }

    public function getMerchantUrls(): MerchantUrls
    {
        return $this->merchantUrls;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getPaymentSessionUrl(): string
    {
        return $this->paymentSessionUrl;
    }

    public function getProfileId(): ?string
    {
        return $this->profileId;
    }

    public function jsonSerialize(): array
    {
        $payload = [
            'merchant_urls' => $this->getMerchantUrls(),
            'options' => $this->getOptions(),
            'payment_session_url' => $this->getPaymentSessionUrl(),
            'profile_id' => $this->getProfileId(),
        ];

        return array_filter($payload, static fn ($value) => $value !== null);
    }
}
