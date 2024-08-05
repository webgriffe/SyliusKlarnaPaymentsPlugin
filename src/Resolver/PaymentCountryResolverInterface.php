<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Resolver;

use InvalidArgumentException;
use Sylius\Component\Core\Model\PaymentInterface;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject\PaymentCountry;

interface PaymentCountryResolverInterface
{
    /**
     * Return the mapping in URL https://docs.klarna.com/klarna-payments/before-you-start/data-requirements/puchase-countries-currencies-locales/
     *
     * @return array<string, PaymentCountry>
     */
    public function getDefaultDataMapping(): array;

    /**
     * @throws InvalidArgumentException
     */
    public function resolve(PaymentInterface $payment): PaymentCountry;
}
