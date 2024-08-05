<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Resolver;

use InvalidArgumentException;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\Country;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\Currency;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\Locale;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\PaymentCountry;

final class PaymentCountryResolver implements PaymentCountryResolverInterface
{
    public function resolve(PaymentInterface $payment): PaymentCountry
    {
        $order = $payment->getOrder();
        if (!$order instanceof OrderInterface) {
            throw new InvalidArgumentException('Order does not exist on payment');
        }
        $billingAddress = $order->getBillingAddress();
        if (!$billingAddress instanceof AddressInterface) {
            throw new InvalidArgumentException('Billing address does not exist on order');
        }
        $countryCode = $billingAddress->getCountryCode();
        if ($countryCode === null) {
            throw new InvalidArgumentException('Country code is not set on billing address');
        }

        $defaultDataMapping = $this->getDefaultDataMapping();
        if (!array_key_exists($countryCode, $defaultDataMapping)) {
            throw new InvalidArgumentException('Could not determine default country code for billing address');
        }

        return $defaultDataMapping[$countryCode];
    }

    /**
     * @return array<string, PaymentCountry>
     */
    public function getDefaultDataMapping(): array
    {
        $paymentCountries = [
            new PaymentCountry(
                Country::Australia,
                [Locale::EnglishAustria],
                Currency::AustralianDollar,
            ),
            new PaymentCountry(
                Country::Austria,
                [Locale::GermanAustria, Locale::EnglishAustria],
                Currency::Euro,
            ),
            new PaymentCountry(
                Country::Belgium,
                [Locale::DutchBelgium, Locale::FrenchBelgium, Locale::EnglishBelgium],
                Currency::Euro,
            ),
            new PaymentCountry(
                Country::Canada,
                [Locale::EnglishCanada, Locale::FrenchCanada],
                Currency::CanadianDollar,
            ),
            new PaymentCountry(
                Country::CzechRepublic,
                [Locale::CzechCzechRepublic, Locale::EnglishCzechRepublic],
                Currency::CzechCrown,
            ),
            new PaymentCountry(
                Country::Denmark,
                [Locale::DanishDenmark, Locale::EnglishDenmark],
                Currency::DanishKrone,
            ),
            new PaymentCountry(
                Country::Finland,
                [Locale::FinnishFinland, Locale::SwedishFinland, Locale::EnglishFinland],
                Currency::Euro,
            ),
            new PaymentCountry(
                Country::France,
                [Locale::FrenchFrance, Locale::EnglishFrance],
                Currency::Euro,
            ),
            new PaymentCountry(
                Country::Germany,
                [Locale::GermanGermany, Locale::EnglishGermany],
                Currency::Euro,
            ),
            new PaymentCountry(
                Country::Greece,
                [Locale::GreekGreece, Locale::EnglishGreece],
                Currency::Euro,
            ),
            new PaymentCountry(
                Country::Hungary,
                [Locale::HungarianHungary, Locale::EnglishHungary],
                Currency::Forint,
            ),
            new PaymentCountry(
                Country::RepublicOfIreland,
                [Locale::EnglishIreland],
                Currency::Euro,
            ),
            new PaymentCountry(
                Country::Italy,
                [Locale::ItalianItaly, Locale::EnglishItaly],
                Currency::Euro,
            ),
            new PaymentCountry(
                Country::Mexico,
                [Locale::EnglishMexico, Locale::SpanishMexico],
                Currency::MexicanPeso,
            ),
            new PaymentCountry(
                Country::Netherlands,
                [Locale::DutchNetherlands, Locale::EnglishNetherlands],
                Currency::Euro,
            ),
            new PaymentCountry(
                Country::NewZealand,
                [Locale::EnglishNewZealand],
                Currency::NewZealandDollar,
            ),
            new PaymentCountry(
                Country::Norway,
                [Locale::NorwegianNorway, Locale::EnglishNorway],
                Currency::NorwegianKrone,
            ),
            new PaymentCountry(
                Country::Poland,
                [Locale::PolishPoland, Locale::EnglishPoland],
                Currency::PolishZloty,
            ),
            new PaymentCountry(
                Country::Portugal,
                [Locale::PortuguesePortugal, Locale::EnglishPortugal],
                Currency::Euro,
            ),
            new PaymentCountry(
                Country::Romania,
                [Locale::RomanianRomania, Locale::EnglishRomania],
                Currency::RomanianLeu,
            ),
            new PaymentCountry(
                Country::Slovakia,
                [Locale::SlovakSlovakia, Locale::EnglishSlovakia],
                Currency::Euro,
            ),
            new PaymentCountry(
                Country::Spain,
                [Locale::SpanishSpain, Locale::EnglishSpain],
                Currency::Euro,
            ),
            new PaymentCountry(
                Country::Sweden,
                [Locale::SwedishSweden, Locale::EnglishSweden],
                Currency::SwedishCrown,
            ),
            new PaymentCountry(
                Country::Switzerland,
                [Locale::GermanSwitzerland, Locale::FrenchSwitzerland, Locale::ItalianSwitzerland, Locale::EnglishSwitzerland],
                Currency::SwissFranc,
            ),
            new PaymentCountry(
                Country::UnitedKingdom,
                [Locale::EnglishUnitedKingdom],
                Currency::BritishPoundSterling,
            ),
            new PaymentCountry(
                Country::UnitedStates,
                [Locale::EnglishUnitedStates, Locale::SpanishUnitedStates],
                Currency::UnitedStatesDollar,
            ),
        ];

        $result = [];
        foreach ($paymentCountries as $paymentCountry) {
            $result[$paymentCountry->getCountry()->value] = $paymentCountry;
        }

        return $result;
    }
}
