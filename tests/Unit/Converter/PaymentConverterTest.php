<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusKlarnaPaymentsPlugin\Unit\Converter;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Address;
use Sylius\Component\Core\Model\Adjustment;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\Model\OrderItemUnit;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductVariant;
use Tests\Webgriffe\SyliusKlarnaPaymentsPlugin\Service\InMemoryTranslator;
use Tests\Webgriffe\SyliusKlarnaPaymentsPlugin\Service\InMemoryUrlGenerator;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\AcquiringChannel;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\Country;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\Currency;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\Intent;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\Locale;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Converter\PaymentConverter;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Resolver\PaymentCountryResolver;

final class PaymentConverterTest extends TestCase
{
    private PaymentConverter $converter;

    private Payment $payment;

    private Order $order;
    private Address $billingAddress;
    private Address $shippingAddress;
    private Product $capProduct;
    private OrderItem $capProductOrderItem;
    private ProductVariant $capProductVariant;

    protected function setUp(): void
    {
        parent::setUp();
        $cacheManager = $this->createMock(CacheManager::class);

        $this->capProduct = new Product();
        $this->capProduct->setCurrentLocale('it_IT');

        $this->capProductVariant = new ProductVariant();
        $this->capProductVariant->setProduct($this->capProduct);

        $this->capProductOrderItem = new OrderItem();
        $this->capProductOrderItem->setUnitPrice(21900);
        $this->capProductOrderItem->setVariant($this->capProductVariant);
        new OrderItemUnit($this->capProductOrderItem);

        $this->billingAddress = new Address();
        $this->billingAddress->setCountryCode('IT');

        $this->shippingAddress = new Address();
        $this->shippingAddress->setCountryCode('IT');

        $this->order = new Order();
        $this->order->setNumber('00000001');
        $this->order->setBillingAddress($this->billingAddress);
        $this->order->setShippingAddress($this->shippingAddress);
        $this->order->setCurrencyCode('EUR');

        $this->payment = new Payment();
        $this->payment->setOrder($this->order);

        $translator = new InMemoryTranslator();
        $translator->setLocale('it_IT');

        $this->converter = new PaymentConverter(
            new PaymentCountryResolver(),
            $translator,
            new InMemoryUrlGenerator(),
            $cacheManager,
            'main',
            'filter',
        );
    }

    public function testItFailsIfPaymentCountryIsNotValid(): void
    {
        $this->billingAddress->setCountryCode('RU');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not determine default country code for billing address having country code \'RU\'');
        $this->converter->convert(
            $this->payment,
            'http://confirmation.url',
            'http://notification.url',
            'http://push.url',
            'http://authorization.url',
        );
    }

    public function testItFailsIfPaymentCurrencyIsNotValidForGivenCountry(): void
    {
        $this->order->setCurrencyCode('USD');
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Attention! The order currency is "USD", but for the country "IT" Klarna only supports currency "EUR". Please, change the channel configuration or implement a way to handle currencies change.');
        $this->converter->convert(
            $this->payment,
            'http://confirmation.url',
            'http://notification.url',
            'http://push.url',
            'http://authorization.url',
        );
    }

    public function testItReturnsValidKlarnaPayment(): void
    {
        $klarnaPayment = $this->converter->convert(
            $this->payment,
            'http://confirmation.url',
            'http://notification.url',
            'http://push.url',
            'http://authorization.url',
        );

        $this->assertEquals(Country::Italy, $klarnaPayment->getPaymentCountry()->getCountry());
        $this->assertEquals([Locale::ItalianItaly, Locale::EnglishItaly], $klarnaPayment->getPaymentCountry()->getLocales());
        $this->assertEquals(Currency::Euro, $klarnaPayment->getPaymentCountry()->getCurrency());
        $this->assertEquals(Intent::buy, $klarnaPayment->getIntent());
        $this->assertEquals(AcquiringChannel::ECOMMERCE, $klarnaPayment->getAcquiringChannel());
        $this->assertEquals(Locale::ItalianItaly, $klarnaPayment->getLocale());
        $this->assertEquals('http://confirmation.url', $klarnaPayment->getMerchantUrls()->getConfirmation());
        $this->assertEquals('http://notification.url', $klarnaPayment->getMerchantUrls()->getNotification());
        $this->assertEquals('http://push.url', $klarnaPayment->getMerchantUrls()->getPush());
        $this->assertEquals('http://authorization.url', $klarnaPayment->getMerchantUrls()->getAuthorization());
        $this->assertNull($klarnaPayment->getCustomer());
        $this->assertEquals('IT', $klarnaPayment->getBillingAddress()->getCountry());
        $this->assertEquals('IT', $klarnaPayment->getShippingAddress()->getCountry());
        $this->assertEquals('#00000001', $klarnaPayment->getMerchantReference1());
        $this->assertNull($klarnaPayment->getMerchantReference2());
        $this->assertEquals('#@', $klarnaPayment->getMerchantData());
    }

    public function testItSetsRightOrderTotalsForSingleProductOrder(): void
    {
        $this->order->addItem($this->capProductOrderItem);
        $klarnaPayment = $this->converter->convert(
            $this->payment,
            'http://confirmation.url',
            'http://notification.url',
            'http://push.url',
            'http://authorization.url',
        );
        $this->assertEquals(21_900, $this->order->getTotal());
        $this->assertCount(1, $this->order->getItems());
        $this->assertEquals(21_900, $this->capProductOrderItem->getTotal());
        $this->assertEquals(0, $this->capProductOrderItem->getTaxTotal());

        $this->assertEquals(21_900, $klarnaPayment->getOrderAmount()->getISO4217Amount());
        $this->assertEquals(0, $klarnaPayment->getOrderTaxAmount()->getISO4217Amount());
        $this->assertEquals(1, $klarnaPayment->getOrderLines()[0]->getQuantity());
        $this->assertEquals(0, $klarnaPayment->getOrderLines()[0]->getTaxRate());
        $this->assertEquals(21_900, $klarnaPayment->getOrderLines()[0]->getTotalAmount()->getISO4217Amount());
        $this->assertEquals(0, $klarnaPayment->getOrderLines()[0]->getTotalDiscountAmount()->getISO4217Amount());
        $this->assertEquals(0, $klarnaPayment->getOrderLines()[0]->getTotalTaxAmount()->getISO4217Amount());
        $this->assertEquals(21_900, $klarnaPayment->getOrderLines()[0]->getUnitPrice()->getISO4217Amount());
    }

    public function testItSetsRightOrderTotalsForSingleProductOrderWithTaxesNotIncluded(): void
    {
        $this->capProductOrderItem->setUnitPrice(17_951);
        $taxAdjustment = new Adjustment();
        $taxAdjustment->setType(Adjustment::TAX_ADJUSTMENT);
        $taxAdjustment->setDetails(['taxRateAmount' => 0.22]);
        $taxAdjustment->setAmount(3_949);
        $taxAdjustment->setNeutral(false);
        $this->capProductOrderItem->addAdjustment($taxAdjustment);

        $this->order->addItem($this->capProductOrderItem);
        $klarnaPayment = $this->converter->convert(
            $this->payment,
            'http://confirmation.url',
            'http://notification.url',
            'http://push.url',
            'http://authorization.url',
        );
        $this->assertEquals(21_900, $this->order->getTotal());
        $this->assertCount(1, $this->order->getItems());
        $this->assertEquals(21_900, $this->capProductOrderItem->getTotal());
        $this->assertEquals(3_949, $this->capProductOrderItem->getTaxTotal());

        $this->assertEquals(21_900, $klarnaPayment->getOrderAmount()->getISO4217Amount());
        $this->assertEquals(3_949, $klarnaPayment->getOrderTaxAmount()->getISO4217Amount());
        $this->assertEquals(1, $klarnaPayment->getOrderLines()[0]->getQuantity());
        $this->assertEquals(2200, $klarnaPayment->getOrderLines()[0]->getTaxRate());
        $this->assertEquals(21_900, $klarnaPayment->getOrderLines()[0]->getTotalAmount()->getISO4217Amount());
        $this->assertEquals(0, $klarnaPayment->getOrderLines()[0]->getTotalDiscountAmount()->getISO4217Amount());
        $this->assertEquals(3_949, $klarnaPayment->getOrderLines()[0]->getTotalTaxAmount()->getISO4217Amount());
        $this->assertEquals(21_900, $klarnaPayment->getOrderLines()[0]->getUnitPrice()->getISO4217Amount());
    }

    public function testItSetsRightOrderTotalsForSingleProductOrderWithTaxesNotIncludedAndMultipleQuantity(): void
    {
        $this->capProductOrderItem->setUnitPrice(17_951);
        $taxAdjustment = new Adjustment();
        $taxAdjustment->setType(Adjustment::TAX_ADJUSTMENT);
        $taxAdjustment->setDetails(['taxRateAmount' => 0.22]);
        $taxAdjustment->setAmount(7_898);
        $taxAdjustment->setNeutral(false);
        $this->capProductOrderItem->addAdjustment($taxAdjustment);

        new OrderItemUnit($this->capProductOrderItem);

        $this->order->addItem($this->capProductOrderItem);
        $klarnaPayment = $this->converter->convert(
            $this->payment,
            'http://confirmation.url',
            'http://notification.url',
            'http://push.url',
            'http://authorization.url',
        );
        $this->assertEquals(43_800, $this->order->getTotal());
        $this->assertCount(1, $this->order->getItems());
        $this->assertEquals(43_800, $this->capProductOrderItem->getTotal());
        $this->assertEquals(7_898, $this->capProductOrderItem->getTaxTotal());

        $this->assertEquals(43_800, $klarnaPayment->getOrderAmount()->getISO4217Amount());
        $this->assertEquals(7_898, $klarnaPayment->getOrderTaxAmount()->getISO4217Amount());
        $this->assertEquals(2, $klarnaPayment->getOrderLines()[0]->getQuantity());
        $this->assertEquals(2200, $klarnaPayment->getOrderLines()[0]->getTaxRate());
        $this->assertEquals(43_800, $klarnaPayment->getOrderLines()[0]->getTotalAmount()->getISO4217Amount());
        $this->assertEquals(0, $klarnaPayment->getOrderLines()[0]->getTotalDiscountAmount()->getISO4217Amount());
        $this->assertEquals(7_898, $klarnaPayment->getOrderLines()[0]->getTotalTaxAmount()->getISO4217Amount());
        $this->assertEquals(21_900, $klarnaPayment->getOrderLines()[0]->getUnitPrice()->getISO4217Amount());
    }

    public function testItSetsRightOrderTotalsForMultipleProductOrderWithTaxesNotIncluded(): void
    {
        $this->capProductOrderItem->setUnitPrice(17_951);
        $taxAdjustment = new Adjustment();
        $taxAdjustment->setType(Adjustment::TAX_ADJUSTMENT);
        $taxAdjustment->setDetails(['taxRateAmount' => 0.22]);
        $taxAdjustment->setAmount(3949);
        $taxAdjustment->setNeutral(false);
        $this->capProductOrderItem->addAdjustment($taxAdjustment);
        $this->order->addItem($this->capProductOrderItem);

        $otherProductProduct = new Product();
        $otherProductProduct->setCurrentLocale('it_IT');
        $otherProductVariant = new ProductVariant();
        $otherProductVariant->setProduct($otherProductProduct);
        $otherProductOrderItem = new OrderItem();
        $otherProductOrderItem->setUnitPrice(5_000);
        $otherProductOrderItem->setVariant($otherProductVariant);
        $taxAdjustment = new Adjustment();
        $taxAdjustment->setType(Adjustment::TAX_ADJUSTMENT);
        $taxAdjustment->setDetails(['taxRateAmount' => 0.22]);
        $taxAdjustment->setAmount(2_200);
        $taxAdjustment->setNeutral(false);
        new OrderItemUnit($otherProductOrderItem);
        new OrderItemUnit($otherProductOrderItem);
        $otherProductOrderItem->addAdjustment($taxAdjustment);
        $this->order->addItem($otherProductOrderItem);

        $klarnaPayment = $this->converter->convert(
            $this->payment,
            'http://confirmation.url',
            'http://notification.url',
            'http://push.url',
            'http://authorization.url',
        );
        $this->assertCount(2, $this->order->getItems());
        $this->assertEquals(34_100, $this->order->getTotal());
        $this->assertEquals(6_149, $this->order->getTaxTotal());
        $this->assertEquals(21_900, $this->capProductOrderItem->getTotal());
        $this->assertEquals(3_949, $this->capProductOrderItem->getTaxTotal());
        $this->assertEquals(12_200, $otherProductOrderItem->getTotal());
        $this->assertEquals(2_200, $otherProductOrderItem->getTaxTotal());

        $this->assertEquals(34_100, $klarnaPayment->getOrderAmount()->getISO4217Amount());
        $this->assertEquals(6_149, $klarnaPayment->getOrderTaxAmount()->getISO4217Amount());

        $this->assertEquals(1, $klarnaPayment->getOrderLines()[0]->getQuantity());
        $this->assertEquals(2200, $klarnaPayment->getOrderLines()[0]->getTaxRate());
        $this->assertEquals(21_900, $klarnaPayment->getOrderLines()[0]->getTotalAmount()->getISO4217Amount());
        $this->assertEquals(0, $klarnaPayment->getOrderLines()[0]->getTotalDiscountAmount()->getISO4217Amount());
        $this->assertEquals(3_949, $klarnaPayment->getOrderLines()[0]->getTotalTaxAmount()->getISO4217Amount());
        $this->assertEquals(21_900, $klarnaPayment->getOrderLines()[0]->getUnitPrice()->getISO4217Amount());

        $this->assertEquals(2, $klarnaPayment->getOrderLines()[1]->getQuantity());
        $this->assertEquals(2200, $klarnaPayment->getOrderLines()[1]->getTaxRate());
        $this->assertEquals(12_200, $klarnaPayment->getOrderLines()[1]->getTotalAmount()->getISO4217Amount());
        $this->assertEquals(0, $klarnaPayment->getOrderLines()[1]->getTotalDiscountAmount()->getISO4217Amount());
        $this->assertEquals(2_200, $klarnaPayment->getOrderLines()[1]->getTotalTaxAmount()->getISO4217Amount());
        $this->assertEquals(6_100, $klarnaPayment->getOrderLines()[1]->getUnitPrice()->getISO4217Amount());
    }

    public function testItSetsRightOrderTotalsForSingleProductOrderWithTaxesIncluded(): void
    {
        $taxAdjustment = new Adjustment();
        $taxAdjustment->setType(Adjustment::TAX_ADJUSTMENT);
        $taxAdjustment->setDetails(['taxRateAmount' => 0.22]);
        $taxAdjustment->setAmount(3949);
        $taxAdjustment->setNeutral(true);
        $this->capProductOrderItem->addAdjustment($taxAdjustment);

        $this->order->addItem($this->capProductOrderItem);
        $klarnaPayment = $this->converter->convert(
            $this->payment,
            'http://confirmation.url',
            'http://notification.url',
            'http://push.url',
            'http://authorization.url',
        );
        $this->assertEquals(21900, $this->order->getTotal());
        $this->assertCount(1, $this->order->getItems());
        $this->assertEquals(21900, $this->capProductOrderItem->getTotal());
        $this->assertEquals(3949, $this->capProductOrderItem->getTaxTotal());

        $this->assertEquals(21900, $klarnaPayment->getOrderAmount()->getISO4217Amount());
        $this->assertEquals(3949, $klarnaPayment->getOrderTaxAmount()->getISO4217Amount());
        $this->assertEquals(1, $klarnaPayment->getOrderLines()[0]->getQuantity());
        $this->assertEquals(2200, $klarnaPayment->getOrderLines()[0]->getTaxRate());
        $this->assertEquals(21900, $klarnaPayment->getOrderLines()[0]->getTotalAmount()->getISO4217Amount());
        $this->assertEquals(0, $klarnaPayment->getOrderLines()[0]->getTotalDiscountAmount()->getISO4217Amount());
        $this->assertEquals(3949, $klarnaPayment->getOrderLines()[0]->getTotalTaxAmount()->getISO4217Amount());
        $this->assertEquals(21900, $klarnaPayment->getOrderLines()[0]->getUnitPrice()->getISO4217Amount());
    }

    public function testItSetsRightOrderTotalsForSingleProductOrderWithTaxesIncludedAndMultipleQuantity(): void
    {
        $taxAdjustment = new Adjustment();
        $taxAdjustment->setType(Adjustment::TAX_ADJUSTMENT);
        $taxAdjustment->setDetails(['taxRateAmount' => 0.22]);
        $taxAdjustment->setAmount(7_898);
        $taxAdjustment->setNeutral(true);
        $this->capProductOrderItem->addAdjustment($taxAdjustment);

        new OrderItemUnit($this->capProductOrderItem);

        $this->order->addItem($this->capProductOrderItem);
        $klarnaPayment = $this->converter->convert(
            $this->payment,
            'http://confirmation.url',
            'http://notification.url',
            'http://push.url',
            'http://authorization.url',
        );
        $this->assertEquals(43_800, $this->order->getTotal());
        $this->assertCount(1, $this->order->getItems());
        $this->assertEquals(43_800, $this->capProductOrderItem->getTotal());
        $this->assertEquals(21_900, $this->capProductOrderItem->getUnitPrice());
        $this->assertEquals(7_898, $this->capProductOrderItem->getTaxTotal());

        $this->assertEquals(43_800, $klarnaPayment->getOrderAmount()->getISO4217Amount());
        $this->assertEquals(7_898, $klarnaPayment->getOrderTaxAmount()->getISO4217Amount());
        $this->assertEquals(2, $klarnaPayment->getOrderLines()[0]->getQuantity());
        $this->assertEquals(2200, $klarnaPayment->getOrderLines()[0]->getTaxRate());
        $this->assertEquals(43_800, $klarnaPayment->getOrderLines()[0]->getTotalAmount()->getISO4217Amount());
        $this->assertEquals(0, $klarnaPayment->getOrderLines()[0]->getTotalDiscountAmount()->getISO4217Amount());
        $this->assertEquals(7_898, $klarnaPayment->getOrderLines()[0]->getTotalTaxAmount()->getISO4217Amount());
        $this->assertEquals(21_900, $klarnaPayment->getOrderLines()[0]->getUnitPrice()->getISO4217Amount());
    }

    public function testItSetsRightOrderTotalsForMultipleProductOrderWithTaxesIncluded(): void
    {
        $taxAdjustment = new Adjustment();
        $taxAdjustment->setType(Adjustment::TAX_ADJUSTMENT);
        $taxAdjustment->setDetails(['taxRateAmount' => 0.22]);
        $taxAdjustment->setAmount(3949);
        $taxAdjustment->setNeutral(true);
        $this->capProductOrderItem->addAdjustment($taxAdjustment);
        $this->order->addItem($this->capProductOrderItem);

        $otherProductProduct = new Product();
        $otherProductProduct->setCurrentLocale('it_IT');
        $otherProductVariant = new ProductVariant();
        $otherProductVariant->setProduct($otherProductProduct);
        $otherProductOrderItem = new OrderItem();
        $otherProductOrderItem->setUnitPrice(6_100);
        $otherProductOrderItem->setVariant($otherProductVariant);
        $taxAdjustment = new Adjustment();
        $taxAdjustment->setType(Adjustment::TAX_ADJUSTMENT);
        $taxAdjustment->setDetails(['taxRateAmount' => 0.22]);
        $taxAdjustment->setAmount(2_200);
        $taxAdjustment->setNeutral(true);
        new OrderItemUnit($otherProductOrderItem);
        new OrderItemUnit($otherProductOrderItem);
        $otherProductOrderItem->addAdjustment($taxAdjustment);
        $this->order->addItem($otherProductOrderItem);

        $klarnaPayment = $this->converter->convert(
            $this->payment,
            'http://confirmation.url',
            'http://notification.url',
            'http://push.url',
            'http://authorization.url',
        );
        $this->assertCount(2, $this->order->getItems());
        $this->assertEquals(34_100, $this->order->getTotal());
        $this->assertEquals(6_149, $this->order->getTaxTotal());
        $this->assertEquals(21_900, $this->capProductOrderItem->getTotal());
        $this->assertEquals(3_949, $this->capProductOrderItem->getTaxTotal());
        $this->assertEquals(12_200, $otherProductOrderItem->getTotal());
        $this->assertEquals(2_200, $otherProductOrderItem->getTaxTotal());

        $this->assertEquals(34_100, $klarnaPayment->getOrderAmount()->getISO4217Amount());
        $this->assertEquals(6_149, $klarnaPayment->getOrderTaxAmount()->getISO4217Amount());

        $this->assertEquals(1, $klarnaPayment->getOrderLines()[0]->getQuantity());
        $this->assertEquals(2200, $klarnaPayment->getOrderLines()[0]->getTaxRate());
        $this->assertEquals(21_900, $klarnaPayment->getOrderLines()[0]->getTotalAmount()->getISO4217Amount());
        $this->assertEquals(0, $klarnaPayment->getOrderLines()[0]->getTotalDiscountAmount()->getISO4217Amount());
        $this->assertEquals(3_949, $klarnaPayment->getOrderLines()[0]->getTotalTaxAmount()->getISO4217Amount());
        $this->assertEquals(21_900, $klarnaPayment->getOrderLines()[0]->getUnitPrice()->getISO4217Amount());

        $this->assertEquals(2, $klarnaPayment->getOrderLines()[1]->getQuantity());
        $this->assertEquals(2200, $klarnaPayment->getOrderLines()[1]->getTaxRate());
        $this->assertEquals(12_200, $klarnaPayment->getOrderLines()[1]->getTotalAmount()->getISO4217Amount());
        $this->assertEquals(0, $klarnaPayment->getOrderLines()[1]->getTotalDiscountAmount()->getISO4217Amount());
        $this->assertEquals(2_200, $klarnaPayment->getOrderLines()[1]->getTotalTaxAmount()->getISO4217Amount());
        $this->assertEquals(6_100, $klarnaPayment->getOrderLines()[1]->getUnitPrice()->getISO4217Amount());
    }

    public function testItSetsRightOrderTotalsForMultipleProductOrderWithMixedTaxesIncludedAndNotIncluded(): void
    {
        $taxAdjustment = new Adjustment();
        $taxAdjustment->setType(Adjustment::TAX_ADJUSTMENT);
        $taxAdjustment->setDetails(['taxRateAmount' => 0.22]);
        $taxAdjustment->setAmount(3949);
        $taxAdjustment->setNeutral(true);
        $this->capProductOrderItem->addAdjustment($taxAdjustment);
        $this->order->addItem($this->capProductOrderItem);

        $otherProductProduct = new Product();
        $otherProductProduct->setCurrentLocale('it_IT');
        $otherProductVariant = new ProductVariant();
        $otherProductVariant->setProduct($otherProductProduct);
        $otherProductOrderItem = new OrderItem();
        $otherProductOrderItem->setUnitPrice(5_000);
        $otherProductOrderItem->setVariant($otherProductVariant);
        $taxAdjustment = new Adjustment();
        $taxAdjustment->setType(Adjustment::TAX_ADJUSTMENT);
        $taxAdjustment->setDetails(['taxRateAmount' => 0.22]);
        $taxAdjustment->setAmount(2_200);
        $taxAdjustment->setNeutral(false);
        new OrderItemUnit($otherProductOrderItem);
        new OrderItemUnit($otherProductOrderItem);
        $otherProductOrderItem->addAdjustment($taxAdjustment);
        $this->order->addItem($otherProductOrderItem);

        $klarnaPayment = $this->converter->convert(
            $this->payment,
            'http://confirmation.url',
            'http://notification.url',
            'http://push.url',
            'http://authorization.url',
        );
        $this->assertCount(2, $this->order->getItems());
        $this->assertEquals(34_100, $this->order->getTotal());
        $this->assertEquals(6_149, $this->order->getTaxTotal());
        $this->assertEquals(21_900, $this->capProductOrderItem->getTotal());
        $this->assertEquals(3_949, $this->capProductOrderItem->getTaxTotal());
        $this->assertEquals(12_200, $otherProductOrderItem->getTotal());
        $this->assertEquals(2_200, $otherProductOrderItem->getTaxTotal());

        $this->assertEquals(34_100, $klarnaPayment->getOrderAmount()->getISO4217Amount());
        $this->assertEquals(6_149, $klarnaPayment->getOrderTaxAmount()->getISO4217Amount());

        $this->assertEquals(1, $klarnaPayment->getOrderLines()[0]->getQuantity());
        $this->assertEquals(2200, $klarnaPayment->getOrderLines()[0]->getTaxRate());
        $this->assertEquals(21_900, $klarnaPayment->getOrderLines()[0]->getTotalAmount()->getISO4217Amount());
        $this->assertEquals(0, $klarnaPayment->getOrderLines()[0]->getTotalDiscountAmount()->getISO4217Amount());
        $this->assertEquals(3_949, $klarnaPayment->getOrderLines()[0]->getTotalTaxAmount()->getISO4217Amount());
        $this->assertEquals(21_900, $klarnaPayment->getOrderLines()[0]->getUnitPrice()->getISO4217Amount());

        $this->assertEquals(2, $klarnaPayment->getOrderLines()[1]->getQuantity());
        $this->assertEquals(2200, $klarnaPayment->getOrderLines()[1]->getTaxRate());
        $this->assertEquals(12_200, $klarnaPayment->getOrderLines()[1]->getTotalAmount()->getISO4217Amount());
        $this->assertEquals(0, $klarnaPayment->getOrderLines()[1]->getTotalDiscountAmount()->getISO4217Amount());
        $this->assertEquals(2_200, $klarnaPayment->getOrderLines()[1]->getTotalTaxAmount()->getISO4217Amount());
        $this->assertEquals(6_100, $klarnaPayment->getOrderLines()[1]->getUnitPrice()->getISO4217Amount());
    }

    public function testItSupportsFreeProducts(): void
    {
        $taxAdjustment = new Adjustment();
        $taxAdjustment->setType(Adjustment::TAX_ADJUSTMENT);
        $taxAdjustment->setDetails(['taxRateAmount' => 0.22]);
        $taxAdjustment->setAmount(3949);
        $taxAdjustment->setNeutral(true);
        $this->capProductOrderItem->addAdjustment($taxAdjustment);
        $this->order->addItem($this->capProductOrderItem);

        $freeProduct = new Product();
        $freeProduct->setCurrentLocale('it_IT');
        $freeProductVariant = new ProductVariant();
        $freeProductVariant->setProduct($freeProduct);
        $freeProductOrderItem = new OrderItem();
        $freeProductOrderItem->setUnitPrice(0);
        $freeProductOrderItem->setVariant($freeProductVariant);
        $taxAdjustment = new Adjustment();
        $taxAdjustment->setType(Adjustment::TAX_ADJUSTMENT);
        $taxAdjustment->setDetails(['taxRateAmount' => 0.22]);
        $taxAdjustment->setAmount(0);
        $taxAdjustment->setNeutral(true);
        $freeProductOrderItem->addAdjustment($taxAdjustment);
        new OrderItemUnit($freeProductOrderItem);
        $this->order->addItem($freeProductOrderItem);

        $klarnaPayment = $this->converter->convert(
            $this->payment,
            'http://confirmation.url',
            'http://notification.url',
            'http://push.url',
            'http://authorization.url',
        );
        $this->assertEquals(21_900, $this->order->getTotal());
        $this->assertCount(2, $this->order->getItems());
        $this->assertEquals(21_900, $this->capProductOrderItem->getTotal());
        $this->assertEquals(3_949, $this->capProductOrderItem->getTaxTotal());

        $this->assertEquals(21_900, $klarnaPayment->getOrderAmount()->getISO4217Amount());
        $this->assertEquals(3_949, $klarnaPayment->getOrderTaxAmount()->getISO4217Amount());

        $this->assertEquals(1, $klarnaPayment->getOrderLines()[0]->getQuantity());
        $this->assertEquals(2200, $klarnaPayment->getOrderLines()[0]->getTaxRate());
        $this->assertEquals(21_900, $klarnaPayment->getOrderLines()[0]->getTotalAmount()->getISO4217Amount());
        $this->assertEquals(0, $klarnaPayment->getOrderLines()[0]->getTotalDiscountAmount()->getISO4217Amount());
        $this->assertEquals(3_949, $klarnaPayment->getOrderLines()[0]->getTotalTaxAmount()->getISO4217Amount());
        $this->assertEquals(21_900, $klarnaPayment->getOrderLines()[0]->getUnitPrice()->getISO4217Amount());

        $this->assertEquals(1, $klarnaPayment->getOrderLines()[1]->getQuantity());
        $this->assertEquals(2200, $klarnaPayment->getOrderLines()[1]->getTaxRate());
        $this->assertEquals(0, $klarnaPayment->getOrderLines()[1]->getTotalAmount()->getISO4217Amount());
        $this->assertEquals(0, $klarnaPayment->getOrderLines()[1]->getTotalDiscountAmount()->getISO4217Amount());
        $this->assertEquals(0, $klarnaPayment->getOrderLines()[1]->getTotalTaxAmount()->getISO4217Amount());
        $this->assertEquals(0, $klarnaPayment->getOrderLines()[1]->getUnitPrice()->getISO4217Amount());
    }
}
