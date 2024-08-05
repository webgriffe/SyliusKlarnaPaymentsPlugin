<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusKlarnaPaymentsPlugin\Resolver\KlarnaPaymentMethodsResolver;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Resolver\PaymentCountryResolver;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Resolver\PaymentCountryResolverInterface;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe_sylius_klarna_payments.resolver.payment_country', PaymentCountryResolver::class);

    $services->alias(PaymentCountryResolverInterface::class, 'webgriffe_sylius_klarna_payments.resolver.payment_country');

    $services->set('webgriffe_sylius_klarna_payments.payment_methods_resolver.klarna', KlarnaPaymentMethodsResolver::class)
        ->args([
            service('sylius.repository.payment_method'),
            service('webgriffe_sylius_klarna_payments.resolver.payment_country'),
        ])
        ->tag('sylius.payment_method_resolver', [
            'type' => 'klarna',
            'label' => 'Klarna',
            'priority' => 2,
        ])
    ;
};
