<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Converter;

use LogicException;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Customer\Model\CustomerInterface as SyliusCustomerInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Address;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Amount;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Order;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\OrderLine;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payments\MerchantUrls;
use Webgriffe\SyliusKlarnaPlugin\Resolver\PaymentCountryResolverInterface;
use Webmozart\Assert\Assert;

final readonly class OrderConverter implements OrderConverterInterface
{
    public function __construct(
        private PaymentCountryResolverInterface $paymentCountryResolver,
    ) {
    }

    public function convert(
        PaymentInterface $payment,
    ): Order {
        $order = $payment->getOrder();
        Assert::isInstanceOf($order, OrderInterface::class);
        $purchaseCountry = $order->getBillingAddress()?->getCountryCode();
        Assert::notNull($purchaseCountry, 'Purchase country is required to create an order on Klarna');
        $purchaseCurrency = $order->getCurrencyCode();
        Assert::notNull($purchaseCurrency, 'Purchase currency is required to create an order on Klarna');
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

        return new Order(
            $paymentCountry,
            $this->getAddress($order->getBillingAddress(), $order->getCustomer()),
            $this->getAddress($order->getShippingAddress(), $order->getCustomer()),
            Amount::fromSyliusAmount($order->getTotal()),
            Amount::fromSyliusAmount($order->getTaxTotal()),
            $this->getOrderLines($order),
            new MerchantUrls(
                '',
                '',
            ),
            (string) $order->getNumber(),
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

    private function getAddress(?AddressInterface $address, ?SyliusCustomerInterface $customer): Address
    {
        Assert::notNull($address);

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
