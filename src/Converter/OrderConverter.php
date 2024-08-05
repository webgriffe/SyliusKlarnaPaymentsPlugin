<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Converter;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use LogicException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Amount;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Order;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Resolver\PaymentCountryResolverInterface;
use Webmozart\Assert\Assert;

final readonly class OrderConverter implements OrderConverterInterface
{
    use CommonOrderConverterTrait;

    public function __construct(
        private PaymentCountryResolverInterface $paymentCountryResolver,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
        private CacheManager $cacheManager,
        private string $mainImageType,
        private string $imageFilter,
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
            Amount::fromSyliusAmount($order->getTotal()),
            Amount::fromSyliusAmount($order->getTaxTotal()),
            $this->getOrderLines($order),
            $paymentCountry->matchUserLocale($order->getLocaleCode()),
            $this->getAddress($order->getBillingAddress(), $order->getCustomer()),
            $this->getAddress($order->getShippingAddress(), $order->getCustomer()),
            false,
            null,
            '#' . (string) $order->getNumber(),
            null,
            $this->getCustomer($order),
            sprintf('#%s@%s', (string) $order->getId(), (string) $payment->getId()),
        );
    }

    private function getUrlGenerator(): UrlGeneratorInterface
    {
        return $this->urlGenerator;
    }

    private function getCacheManager(): CacheManager
    {
        return $this->cacheManager;
    }

    private function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    private function getSchema(): string
    {
        return 'https';
    }

    private function getImageFilter(): string
    {
        return $this->imageFilter;
    }

    private function getMainImageType(): string
    {
        return $this->mainImageType;
    }
}
