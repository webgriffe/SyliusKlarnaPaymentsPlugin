<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

use Webgriffe\SyliusKlarnaPlugin\Client\Enum\AcquiringChannel;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\Intent;

final readonly class Payment
{
    /**
     * @param OrderLine[] $orderLines
     */
    public function __construct(
        private AcquiringChannel $acquiringChannel,
        private Customer $customer,
        private Address $billingAddress,
        private Address $shippingAddress,
        private string $locale,
        private string $merchantReference1,
        private string $merchantReference2,
        private MerchantUrls $merchantUrls,
        private int $orderAmount,
        private array $orderLines,
        private int $orderTaxAmount,
        private string $purchaseCountry,
        private string $purchaseCurrency,
        private Intent $intent,
    ) {
    }
}
