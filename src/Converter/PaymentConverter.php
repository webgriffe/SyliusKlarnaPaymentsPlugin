<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Converter;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use LogicException;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Customer\Model\CustomerInterface as ModelCustomerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\AcquiringChannel;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\Intent;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\OrderLineType;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Address;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Amount;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Customer;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\OrderLine;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payment;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Payments\MerchantUrls;
use Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\ProductIdentifiers;
use Webgriffe\SyliusKlarnaPlugin\Resolver\PaymentCountryResolverInterface;
use Webmozart\Assert\Assert;

final readonly class PaymentConverter implements PaymentConverterInterface
{
    public function __construct(
        private PaymentCountryResolverInterface $paymentCountryResolver,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
        private CacheManager $cacheManager,
    ) {
    }

    public function convert(
        PaymentInterface $payment,
        ?string $confirmationUrl,
        ?string $notificationUrl,
        ?string $pushUrl,
        ?string $authorizationUrl,
    ): Payment {
        $order = $payment->getOrder();
        Assert::isInstanceOf($order, OrderInterface::class);
        $billingAddress = $order->getBillingAddress();
        $purchaseCountry = $billingAddress?->getCountryCode();
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
        $merchantUrls = null;
        if ($confirmationUrl !== null || $notificationUrl !== null || $pushUrl !== null || $authorizationUrl !== null) {
            $merchantUrls = new MerchantUrls(
                $confirmationUrl,
                $notificationUrl,
                $pushUrl,
                $authorizationUrl,
            );
        }

        return new Payment(
            $paymentCountry,
            Amount::fromSyliusAmount($order->getTotal()),
            $this->getOrderLines($order),
            Intent::buy,
            AcquiringChannel::ECOMMERCE,
            $paymentCountry->matchUserLocale($order->getLocaleCode()),
            $merchantUrls,
            $this->getCustomer($order),
            $this->getAddress($billingAddress, $order->getCustomer()),
            $this->getAddress($order->getShippingAddress(), $order->getCustomer()),
            (string) $order->getNumber(),
            null,
            Amount::fromSyliusAmount($order->getTaxTotal()),
            sprintf('#%s@%s', $order->getId(), $payment->getId()),
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
        $taxRate = $this->getOrderTaxRate($order);
        $previousContext = $this->urlGenerator->getContext();
        $hostname = $order->getChannel()?->getHostname();
        if ($hostname !== null) {
            $this->urlGenerator->setContext(new RequestContext(
                '',
                'GET',
                $hostname,
                'https',
            ));
        }
        $slug = $orderItem->getProduct()?->getSlug();
        $productUrl = null;
        if ($slug !== null) {
            $productUrl = $this->urlGenerator->generate(
                'sylius_shop_product_show',
                ['slug' => $slug],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );
        }
        $productImagePath = $this->getProductImagePath($orderItem->getProduct());
        $imageUrl = null;
        if ($productImagePath !== null) {
            $this->cacheManager->getBrowserPath(
                $productImagePath,
                'sylius_shop_product_thumbnail',
            );
        }
        $this->urlGenerator->setContext($previousContext);

        $productIdentifiers = null;
        if ($product !== null) {
            $productIdentifiers = new ProductIdentifiers(
                null,
                $this->getCategoryPath($product),
            );
        }

        return new OrderLine(
            (string) $orderItem->getProductName(),
            $orderItem->getQuantity(),
            $taxRate,
            Amount::fromSyliusAmount($orderItem->getTotal()),
            Amount::fromSyliusAmount($orderItem->getFullDiscountedUnitPrice() * $orderItem->getQuantity()),
            Amount::fromSyliusAmount($orderItem->getTaxTotal()),
            Amount::fromSyliusAmount($orderItem->getUnitPrice()),
            $productUrl,
            $imageUrl,
            $orderItem->getId(),
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
        $shippingTaxTotal = $totalAmount - (($totalAmount * 10000) / (10000 + $taxRate));

        return new OrderLine(
            $shipment->getMethod()?->getName() ?? $this->translator->trans('sylius.ui.shipping_charges'),
            1,
            $taxRate,
            Amount::fromSyliusAmount($totalAmount),
            Amount::fromSyliusAmount($order->getAdjustmentsTotalRecursively(AdjustmentInterface::ORDER_SHIPPING_PROMOTION_ADJUSTMENT)),
            Amount::fromSyliusAmount((int) $shippingTaxTotal),
            Amount::fromSyliusAmount($totalAmount),
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
        $taxAdjustment = $order->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT)->first();
        if ($taxAdjustment instanceof AdjustmentInterface) {
            $taxRate = (int) ($taxAdjustment->getDetails()['taxRateAmount'] * 10000);
        }

        return $taxRate;
    }

    private function getProductImagePath(?ProductInterface $product): ?string
    {
        if ($product === null) {
            return null;
        }
        $images = $product->getImagesByType('main');
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
            $categoryPath .= $taxon->getName() . ' > ';
        }

        return $categoryPath . ' > ' . $mainTaxon->getName();
    }
}
