<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

use JsonException;
use JsonSerializable;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\AcquiringChannel;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\Intent;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payments\MerchantUrls;

final readonly class Payment implements JsonSerializable
{
    /**
     * @param OrderLine[] $orderLines
     */
    public function __construct(
        private PaymentCountry $paymentCountry,
        private Amount $orderAmount,
        private array $orderLines,
        private Intent $intent = Intent::buy,
        private AcquiringChannel $acquiringChannel = AcquiringChannel::ECOMMERCE,
        private ?string $userLocale = null,
        private ?MerchantUrls $merchantUrls = null,
        private ?Customer $customer = null,
        private ?Address $billingAddress = null,
        private ?Address $shippingAddress = null,
        private ?string $merchantReference1 = null,
        private ?string $merchantReference2 = null,
        private ?Amount $orderTaxAmount = null,
        private ?string $merchantData = null,
    ) {
    }

    public function getUserLocale(): ?string
    {
        return $this->userLocale;
    }

    public function getPaymentCountry(): PaymentCountry
    {
        return $this->paymentCountry;
    }

    public function getOrderAmount(): Amount
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

    public function getMerchantUrls(): ?MerchantUrls
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

    public function getOrderTaxAmount(): ?Amount
    {
        return $this->orderTaxAmount;
    }

    public function getMerchantData(): ?string
    {
        return $this->merchantData;
    }

    /**
     * @throws JsonException
     */
    public function jsonSerialize(): array
    {
        $payload = [
            'locale' => $this->getPaymentCountry()->matchUserLocale($this->getUserLocale())->value,
            'purchase_country' => $this->getPaymentCountry()->getCountry()->value,
            'purchase_currency' => $this->getPaymentCountry()->getCurrency()->value,
            'order_amount' => $this->getOrderAmount()->getISO4217Amount(),
            'order_lines' => $this->getOrderLines(),
            'intent' => $this->getIntent()->value,
            'merchant_urls' => $this->getMerchantUrls(),
            'acquiring_channel' => $this->getAcquiringChannel()->value,
            'customer' => $this->getCustomer(),
            'billing_address' => $this->getBillingAddress(),
            'shipping_address' => $this->getShippingAddress(),
            'merchant_reference1' => $this->getMerchantReference1(),
            'merchant_reference2' => $this->getMerchantReference2(),
            'order_tax_amount' => $this->getOrderTaxAmount()?->getISO4217Amount(),
            'merchant_data' => $this->getMerchantData(),
        ];

        return array_filter($payload, static fn ($value) => $value !== null);
    }
}
