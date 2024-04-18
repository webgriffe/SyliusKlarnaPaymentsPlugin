<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

use JsonException;
use JsonSerializable;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\AcquiringChannel;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\Intent;

final readonly class Payment implements JsonSerializable
{
    /**
     * @param OrderLine[] $orderLines
     */
    public function __construct(
        private AcquiringChannel $acquiringChannel,
        private Customer $customer,
        private Address $billingAddress,
        private Address $shippingAddress,
        private string $locale,
        private string $merchantReference1,
        private string $merchantReference2,
        private MerchantUrls $merchantUrls,
        private int $orderAmount,
        private array $orderLines,
        private int $orderTaxAmount,
        private string $purchaseCountry,
        private string $purchaseCurrency,
        private Intent $intent,
    ) {
    }

    /**
     * @throws JsonException
     */
    public function jsonSerialize(): array
    {
        return [
            'acquiring_channel' => json_encode($this->acquiringChannel, \JSON_THROW_ON_ERROR),
            'customer' => $this->customer,
            'billing_address' => $this->billingAddress,
            'shipping_address' => $this->shippingAddress,
            'locale' => $this->locale,
            'merchant_reference1' => $this->merchantReference1,
            'merchant_reference2' => $this->merchantReference2,
            'merchant_urls' => $this->merchantUrls,
            'order_amount' => $this->orderAmount,
            'order_lines' => $this->orderLines,
            'order_tax_amount' => $this->orderTaxAmount,
            'purchase_country' => $this->purchaseCountry,
            'purchase_currency' => $this->purchaseCurrency,
            'intent' => json_encode($this->intent, \JSON_THROW_ON_ERROR),
        ];
    }
}
