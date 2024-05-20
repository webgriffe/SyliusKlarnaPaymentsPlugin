<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

use Webgriffe\SyliusKlarnaPlugin\Client\Enum\Country;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\Currency;
use Webgriffe\SyliusKlarnaPlugin\Client\Enum\Locale;

final readonly class PaymentCountry
{
    /**
     * @param Locale[] $locales
     */
    public function __construct(
        private Country $country,
        private array $locales,
        private Currency $currency,
    ) {
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function getLocales(): array
    {
        return $this->locales;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }
}
