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
        private string $locale,
        private string $purchaseCountry,
        private string $purchaseCurrency,
        private int $orderAmount,
        private array $orderLines,
        private Intent $intent,
        private MerchantUrls $merchantUrls,
        private AcquiringChannel $acquiringChannel = AcquiringChannel::ECOMMERCE,
        private ?Customer $customer = null,
        private ?Address $billingAddress = null,
        private ?Address $shippingAddress = null,
        private ?string $merchantReference1 = null,
        private ?string $merchantReference2 = null,
        private ?int $orderTaxAmount = null,
    ) {
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getPurchaseCountry(): string
    {
        return $this->purchaseCountry;
    }

    public function getPurchaseCurrency(): string
    {
        return $this->purchaseCurrency;
    }

    public function getOrderAmount(): int
    {
        return $this->orderAmount;
    }

    public function getOrderLines(): array
    {
        return $this->orderLines;
    }

    public function getIntent(): Intent
    {
        return $this->intent;
    }

    public function getMerchantUrls(): MerchantUrls
    {
        return $this->merchantUrls;
    }

    public function getAcquiringChannel(): AcquiringChannel
    {
        return $this->acquiringChannel;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    public function getShippingAddress(): ?Address
    {
        return $this->shippingAddress;
    }

    public function getMerchantReference1(): ?string
    {
        return $this->merchantReference1;
    }

    public function getMerchantReference2(): ?string
    {
        return $this->merchantReference2;
    }

    public function getOrderTaxAmount(): ?int
    {
        return $this->orderTaxAmount;
    }

    /**
     * @throws JsonException
     */
    public function jsonSerialize(): array
    {
        $payload = [
            'locale' => $this->getLocale(),
            'purchase_country' => $this->getPurchaseCountry(),
            'purchase_currency' => $this->getPurchaseCurrency(),
            'order_amount' => $this->getISO4217Amount(),
            'order_lines' => $this->getOrderLines(),
            'intent' => $this->getIntent()->value,
            'merchant_urls' => $this->getMerchantUrls(),
            'acquiring_channel' => $this->getAcquiringChannel()->value,
            'customer' => $this->getCustomer(),
            'billing_address' => $this->getBillingAddress(),
            'shipping_address' => $this->getShippingAddress(),
            'merchant_reference1' => $this->getMerchantReference1(),
            'merchant_reference2' => $this->getMerchantReference2(),
            'order_tax_amount' => $this->getOrderTaxAmount(),
        ];

        return array_filter($payload, static fn ($value) => $value !== null);
    }

    private function getISO4217Amount(): int
    {
        if ($this->getPurchaseCurrency() === 'EUR') {
            return (int) ($this->getOrderAmount() / 100);
        }

        return $this->getOrderAmount();
    }
}
