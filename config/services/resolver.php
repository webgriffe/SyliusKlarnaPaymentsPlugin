<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusKlarnaPlugin\Resolver\KlarnaPaymentMethodsResolver;
use Webgriffe\SyliusKlarnaPlugin\Resolver\PaymentCountryResolver;
use Webgriffe\SyliusKlarnaPlugin\Resolver\PaymentCountryResolverInterface;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe_sylius_klarna.resolver.payment_country', PaymentCountryResolver::class);

    $services->alias(PaymentCountryResolverInterface::class, 'webgriffe_sylius_klarna.resolver.payment_country');

    $services->set('webgriffe_sylius_klarna.payment_methods_resolver.klarna', KlarnaPaymentMethodsResolver::class)
        ->args([
            service('sylius.repository.payment_method'),
            service('webgriffe_sylius_klarna.resolver.payment_country'),
        ])
        ->tag('sylius.payment_method_resolver', [
            'type' => 'klarna',
            'label' => 'Klarna',
            'priority' => 2,
        ])
    ;
};
