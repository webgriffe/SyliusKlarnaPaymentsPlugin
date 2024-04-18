<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Converter;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\AcquiringChannel;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\Intent;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Address;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Customer;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\MerchantUrls;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payment;
use Webmozart\Assert\Assert;

final class PaymentConverter implements PaymentConverterInterface
{
    public function convert(PaymentInterface $payment): Payment
    {
        $order = $payment->getOrder();
        Assert::isInstanceOf($order, OrderInterface::class);

        return new Payment(
            AcquiringChannel::ECOMMERCE,
            new Customer(),
            new Address(),
            new Address(),
            $order->getLocaleCode(),
            'merchantReference1',
            'merchantReference2',
            new MerchantUrls(),
            $order->getTotal(),
            [],
            $order->getTaxTotal(),
            $order->getBillingAddress()?->getCountryCode(),
            $order->getCurrencyCode(),
            Intent::buy,
        );
    }
}
