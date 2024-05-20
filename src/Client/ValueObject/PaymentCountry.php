<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

use InvalidArgumentException;
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

    /**
     * @return Locale[]
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function matchUserLocale(?string $userLocale): Locale
    {
        $countryLocales = $this->getLocales();
        if ($countryLocales === []) {
            throw new InvalidArgumentException('There is no locale set for the country!');
        }
        if ($userLocale === null || count($countryLocales) === 1) {
            return reset($countryLocales);
        }
        $matchingLanguageLocales = [];
        $userLanguage = $userLocale;
        if (str_contains($userLanguage, '_')) {
            $userLanguage = explode('_', $userLanguage)[0];
        }
        foreach ($countryLocales as $locale) {
            if ($userLocale === $locale->value || str_replace('_', '-', $userLocale) === $locale->value) {
                return $locale;
            }
            if ($this->getLanguageFromCountry($locale) === $userLanguage) {
                $matchingLanguageLocales[] = $locale;
            }
        }

        if ($matchingLanguageLocales !== []) {
            return reset($matchingLanguageLocales);
        }

        return reset($countryLocales);
    }

    private function getLanguageFromCountry(Locale $locale): string
    {
        $localeCode = $locale->value;
        if (!str_contains($localeCode, '-')) {
            return $localeCode;
        }

        return explode('-', $localeCode)[0];
    }
}
