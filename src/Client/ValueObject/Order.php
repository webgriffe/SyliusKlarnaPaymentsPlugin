<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject;

use JsonSerializable;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\Locale;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Payments\MerchantUrls;

final readonly class Order implements JsonSerializable
{
    /**
     * @param OrderLine[] $orderLines
     * @param string[]|null $customPaymentMethodIds
     */
    public function __construct(
        private PaymentCountry $paymentCountry,
        private Amount $amount,
        private Amount $taxAmount,
        private array $orderLines,
        private Locale $locale = Locale::EnglishUnitedStates,
        private ?Address $billingAddress = null,
        private ?Address $shippingAddress = null,
        private bool $autoCapture = false,
        private ?MerchantUrls $merchantUrls = null,
        private ?string $merchantReference1 = null,
        private ?string $merchantReference2 = null,
        private ?Customer $customer = null,
        private ?string $merchantData = null,
        private ?array $customPaymentMethodIds = null,
    ) {
    }

    public function isAutoCapture(): bool
    {
        return $this->autoCapture;
    }

    public function getPaymentCountry(): PaymentCountry
    {
        return $this->paymentCountry;
    }

    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    public function getShippingAddress(): ?Address
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

    /**
     * @return OrderLine[]
     */
    public function getOrderLines(): array
    {
        return $this->orderLines;
    }

    public function getMerchantUrls(): ?MerchantUrls
    {
        return $this->merchantUrls;
    }

    public function getMerchantReference1(): ?string
    {
        return $this->merchantReference1;
    }

    public function getMerchantReference2(): ?string
    {
        return $this->merchantReference2;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    /**
     * @return string[]|null
     */
    public function getCustomPaymentMethodIds(): ?array
    {
        return $this->customPaymentMethodIds;
    }

    public function getMerchantData(): ?string
    {
        return $this->merchantData;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'auto_capture' => $this->isAutoCapture(),
            'billing_address' => $this->getBillingAddress(),
            'custom_payment_method_ids' => $this->getCustomPaymentMethodIds(),
            'customer' => $this->getCustomer(),
            'locale' => $this->getLocale(),
            'merchant_data' => $this->getMerchantData(),
            'merchant_reference1' => $this->getMerchantReference1(),
            'merchant_reference2' => $this->getMerchantReference2(),
            'merchant_urls' => $this->getMerchantUrls(),
            'order_amount' => $this->getAmount()->getISO4217Amount(),
            'order_lines' => $this->getOrderLines(),
            'order_tax_amount' => $this->getTaxAmount()->getISO4217Amount(),
            'purchase_country' => $this->getPaymentCountry()->getCountry()->value,
            'purchase_currency' => $this->getPaymentCountry()->getCurrency()->value,
            'shipping_address' => $this->getShippingAddress(),
        ], static fn ($value): bool => $value !== null);
    }
}
