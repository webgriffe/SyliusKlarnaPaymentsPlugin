<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Converter;

use LogicException;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\AcquiringChannel;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\Intent;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Address;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Amount;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Customer;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\OrderLine;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payment;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payments\MerchantUrls;
use Webgriffe\SyliusKlarnaPlugin\Resolver\PaymentCountryResolverInterface;
use Webmozart\Assert\Assert;

final readonly class PaymentConverter implements PaymentConverterInterface
{
    public function __construct(
        private PaymentCountryResolverInterface $paymentCountryResolver,
    ) {
    }

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
        $paymentCountry = $this->paymentCountryResolver->resolve($payment);
        if ($purchaseCurrency !== $paymentCountry->getCurrency()->value) {
            throw new LogicException(sprintf(
                'Attention! The order currency is "%s", but for the country "%s" Klarna only supports currency
                "%s". Please, change the channel configuration or implement a way to handle currencies change',
                $purchaseCurrency,
                $purchaseCountry,
                $paymentCountry->getCurrency()->value,
            ));
        }

        return new Payment(
            $paymentCountry,
            Amount::fromSyliusAmount($order->getTotal()),
            $this->getOrderLines($order),
            Intent::buy,
            AcquiringChannel::ECOMMERCE,
            $paymentCountry->matchUserLocale($order->getLocaleCode()),
            new MerchantUrls(
                $confirmationUrl,
                $notificationUrl,
            ),
            $this->getCustomer($order),
            $this->getAddress($order->getBillingAddress(), $order->getCustomer()),
            $this->getAddress($order->getShippingAddress(), $order->getCustomer()),
            (string) $order->getNumber(),
            (string) $payment->getId(),
            Amount::fromSyliusAmount($order->getTaxTotal()),
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
            2200,
            Amount::fromSyliusAmount($orderItem->getTotal()),
            Amount::fromSyliusAmount(0),
            Amount::fromSyliusAmount($orderItem->getTaxTotal()),
            Amount::fromSyliusAmount($orderItem->getUnitPrice()),
            null,
            null,
            null,
            'pcs',
            $orderItem->getProduct()?->getCode(),
            'physical',
        );
    }

    private function getAddress(?AddressInterface $address, ?\Sylius\Component\Customer\Model\CustomerInterface $customer): ?Address
    {
        if (!$address instanceof AddressInterface) {
            return null;
        }

        $region = $address->getProvinceCode();
        if ($region !== null && str_contains($region, '-')) {
            $region = explode('-', $region)[1];
        }

        return new Address(
            $address->getCity(),
            $address->getCountryCode(),
            $customer?->getEmail(),
            $address->getLastName(),
            $address->getFirstName(),
            $address->getPhoneNumber(),
            $address->getPostcode(),
            $region,
            $address->getStreet(),
            null,
            null,
        );
    }
}
