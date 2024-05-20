<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

use JsonSerializable;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payments\MerchantUrls;

final readonly class Order implements JsonSerializable
{
    public function __construct(
        private PaymentCountry $paymentCountry,
        private Address $billingAddress,
        private Address $shippingAddress,
        private Amount $amount,
        private Amount $taxAmount,
        private array $lines,
        private MerchantUrls $merchantUrls,
        private string $merchantReference1,
    ) {
    }

    public function getPaymentCountry(): PaymentCountry
    {
        return $this->paymentCountry;
    }

    public function getBillingAddress(): Address
    {
        return $this->billingAddress;
    }

    public function getShippingAddress(): Address
    {
        return $this->shippingAddress;
    }

    public function getAmount(): Amount
    {
        return $this->amount;
    }

    public function getTaxAmount(): Amount
    {
        return $this->taxAmount;
    }

    public function getLines(): array
    {
        return $this->lines;
    }

    public function getMerchantUrls(): MerchantUrls
    {
        return $this->merchantUrls;
    }

    public function getMerchantReference1(): string
    {
        return $this->merchantReference1;
    }

    public function jsonSerialize(): array
    {
        return [
            'purchase_country' => $this->getPaymentCountry()->getCountry()->value,
            'purchase_currency' => $this->getPaymentCountry()->getCurrency()->value,
            'billing_address' => $this->getBillingAddress(),
            'shipping_address' => $this->getShippingAddress(),
            'order_amount' => $this->getAmount()->getISO4217Amount(),
            'order_tax_amount' => $this->getTaxAmount()->getISO4217Amount(),
            'order_lines' => $this->getLines(),
            'merchant_urls' => $this->getMerchantUrls(),
            'merchant_reference1' => $this->getMerchantReference1(),
        ];
    }
}
