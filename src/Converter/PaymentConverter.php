<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Converter;

use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\AcquiringChannel;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\Intent;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Address;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Customer;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\OrderLine;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payment;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payments\MerchantUrls;
use Webmozart\Assert\Assert;

final class PaymentConverter implements PaymentConverterInterface
{
    public function convert(
        PaymentInterface $payment,
        string $confirmationUrl,
        string $notificationUrl,
    ): Payment {
        $order = $payment->getOrder();
        Assert::isInstanceOf($order, OrderInterface::class);
        $purchaseCountry = $order->getBillingAddress()?->getCountryCode();
        Assert::notNull($purchaseCountry, 'Purchase country is required to create a payment on Klarna');
        $purchaseCurrency = $order->getCurrencyCode();
        Assert::notNull($purchaseCurrency, 'Purchase currency is required to create a payment on Klarna');

        return new Payment(
            str_replace('_', '-', (string) $order->getLocaleCode()),
            $purchaseCountry,
            $purchaseCurrency,
            $order->getTotal(),
            $this->getOrderLines($order),
            Intent::buy,
            new MerchantUrls($confirmationUrl, $notificationUrl),
            AcquiringChannel::ECOMMERCE,
            $this->getCustomer($order),
            $this->getAddress($order->getBillingAddress(), $order->getCustomer()),
            $this->getAddress($order->getShippingAddress(), $order->getCustomer()),
            (string) $order->getNumber(),
            null,
            $order->getTaxTotal(),
        );
    }

    private function getCustomer(OrderInterface $order): ?Customer
    {
        $customer = $order->getCustomer();
        if (!$customer instanceof CustomerInterface) {
            return null;
        }
        $isMale = null;
        $gender = $customer->getGender();
        if ($gender !== CustomerInterface::UNKNOWN_GENDER) {
            $isMale = $gender === CustomerInterface::MALE_GENDER;
        }

        return new Customer(
            $customer->getBirthday(),
            $isMale,
            null,
            null,
            null,
        );
    }

    /**
     * @return OrderLine[]
     */
    private function getOrderLines(OrderInterface $order): array
    {
        $lines = [];
        foreach ($order->getItems() as $orderItem) {
            $lines[] = $this->createOrderLineFromOrderItem($orderItem);
        }

        return $lines;
    }

    private function createOrderLineFromOrderItem(OrderItemInterface $orderItem): OrderLine
    {
        return new OrderLine(
            (string) $orderItem->getProductName(),
            $orderItem->getQuantity(),
            $orderItem->getTotal(),
            $orderItem->getUnitPrice(),
            null,
            null,
            $orderItem->getProduct()?->getCode(),
        );
    }

    private function getAddress(?AddressInterface $address, ?\Sylius\Component\Customer\Model\CustomerInterface $customer): ?Address
    {
        if (!$address instanceof AddressInterface) {
            return null;
        }

        return new Address(
            $address->getCity(),
            $address->getCountryCode(),
            $customer?->getEmail(),
            $address->getLastName(),
            $address->getFirstName(),
            $address->getPhoneNumber(),
            $address->getPostcode(),
            $address->getProvinceCode(),
            $address->getStreet(),
            null,
            null,
        );
    }
}
