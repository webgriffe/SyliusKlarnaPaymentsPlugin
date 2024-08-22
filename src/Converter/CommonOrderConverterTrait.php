<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Converter;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\OrderItemUnitInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Customer\Model\CustomerInterface as ModelCustomerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\OrderLineType;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Address;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Amount;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\Customer;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\OrderLine;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\ProductIdentifiers;

trait CommonOrderConverterTrait
{
    abstract private function getUrlGenerator(): UrlGeneratorInterface;

    abstract private function getCacheManager(): CacheManager;

    abstract private function getTranslator(): TranslatorInterface;

    abstract private function getSchema(): string;

    abstract private function getImageFilter(): string;

    abstract private function getMainImageType(): string;

    private function getCustomer(OrderInterface $order): ?Customer
    {
        $customer = $order->getCustomer();
        if (!$customer instanceof CustomerInterface) {
            return null;
        }
        $isMale = null;
        $gender = $customer->getGender();
        if ($gender !== ModelCustomerInterface::UNKNOWN_GENDER) {
            $isMale = $gender === ModelCustomerInterface::MALE_GENDER;
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
            $lines[] = $this->createOrderLineFromOrderItem($order, $orderItem);
        }
        $shipment = $order->getShipments()->first();
        if ($shipment instanceof ShipmentInterface) {
            $lines[] = $this->createOrderLineFromShipment($order, $shipment);
        }

        return $lines;
    }

    private function getAddress(?AddressInterface $address, ?ModelCustomerInterface $customer): ?Address
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

    private function createOrderLineFromOrderItem(OrderInterface $order, OrderItemInterface $orderItem): OrderLine
    {
        $product = $orderItem->getProduct();
        $previousContext = $this->getUrlGenerator()->getContext();
        $hostname = $order->getChannel()?->getHostname();
        if ($hostname !== null) {
            $this->getUrlGenerator()->setContext(new RequestContext(
                '',
                'GET',
                $hostname,
                $this->getSchema(),
            ));
        }
        $slug = $orderItem->getProduct()?->getSlug();
        $productUrl = null;
        if ($slug !== null) {
            $productUrl = $this->getUrlGenerator()->generate(
                'sylius_shop_product_show',
                ['slug' => $slug],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );
        }
        $productImagePath = $this->getProductImagePath($orderItem->getProduct());
        $imageUrl = null;
        if ($productImagePath !== null) {
            $imageUrl = $this->getCacheManager()->getBrowserPath(
                $productImagePath,
                $this->getImageFilter(),
            );
        }
        $this->getUrlGenerator()->setContext($previousContext);

        $productIdentifiers = null;
        if ($product !== null) {
            $productIdentifiers = new ProductIdentifiers(
                null,
                $this->getCategoryPath($product),
            );
        }
        $taxRate = $this->getOrderTaxRate($order);
        $totalAmount = $orderItem->getTotal();
        $totalTaxAmount = (int) ($totalAmount - (($totalAmount * 10000) / (10000 + $taxRate)));
        $totalDiscountAmount = 0;
        $firstOrderItemUnit = $orderItem->getUnits()->first();
        if ($firstOrderItemUnit instanceof OrderItemUnitInterface) {
            $totalDiscountAmount = -1 * $firstOrderItemUnit->getAdjustmentsTotal(AdjustmentInterface::ORDER_PROMOTION_ADJUSTMENT);
        }

        return new OrderLine(
            (string) $orderItem->getProductName(),
            $orderItem->getQuantity(),
            $taxRate,
            Amount::fromSyliusAmount($totalAmount),
            Amount::fromSyliusAmount($totalDiscountAmount * $orderItem->getQuantity()),
            Amount::fromSyliusAmount($totalTaxAmount),
            Amount::fromSyliusAmount($orderItem->getUnitPrice()),
            $productUrl,
            $imageUrl,
            (string) $orderItem->getId(),
            'pcs',
            $orderItem->getProduct()?->getCode(),
            OrderLineType::Physical,
            $productIdentifiers,
            null,
        );
    }

    private function createOrderLineFromShipment(OrderInterface $order, ShipmentInterface $shipment): OrderLine
    {
        $taxRate = $this->getOrderTaxRate($order);
        $totalAmount = $order->getShippingTotal();
        $unitPrice = $order->getAdjustmentsTotalRecursively(AdjustmentInterface::SHIPPING_ADJUSTMENT);
        $shippingTaxTotal = (int) ($totalAmount - (($totalAmount * 10000) / (10000 + $taxRate)));
        $totalDiscountAmount = -1 * $order->getAdjustmentsTotalRecursively(AdjustmentInterface::ORDER_SHIPPING_PROMOTION_ADJUSTMENT);

        return new OrderLine(
            $shipment->getMethod()?->getName() ?? $this->getTranslator()->trans('sylius.ui.shipping_charges'),
            1,
            $taxRate,
            Amount::fromSyliusAmount($totalAmount),
            Amount::fromSyliusAmount($totalDiscountAmount),
            Amount::fromSyliusAmount($shippingTaxTotal),
            Amount::fromSyliusAmount($unitPrice),
            null,
            null,
            null,
            null,
            null,
            OrderLineType::ShippingFee,
        );
    }

    private function getOrderTaxRate(OrderInterface $order): int
    {
        $taxRate = 0;
        $taxAdjustment = $order->getAdjustmentsRecursively(AdjustmentInterface::TAX_ADJUSTMENT)->first();
        if ($taxAdjustment instanceof AdjustmentInterface) {
            /** @var float $taxRateAmount */
            $taxRateAmount = $taxAdjustment->getDetails()['taxRateAmount'];
            $taxRate = (int) ($taxRateAmount * 10000);
        }

        return $taxRate;
    }

    private function getProductImagePath(?ProductInterface $product): ?string
    {
        if ($product === null) {
            return null;
        }
        $images = $product->getImagesByType($this->getMainImageType());
        foreach ($images as $image) {
            return $image->getPath();
        }
        $images = $product->getImages();
        foreach ($images as $image) {
            return $image->getPath();
        }

        return null;
    }

    private function getCategoryPath(ProductInterface $product): ?string
    {
        $categoryPath = '';
        $mainTaxon = $product->getMainTaxon();
        if ($mainTaxon === null) {
            return null;
        }
        foreach ($mainTaxon->getAncestors() as $taxon) {
            $categoryPath = (string) $taxon->getName() . ' > ' . $categoryPath;
        }

        return $categoryPath . (string) $mainTaxon->getName();
    }
}
