<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusKlarnaPlugin\Converter\HostedPaymentPageConverter;
use Webgriffe\SyliusKlarnaPlugin\Converter\OrderConverter;
use Webgriffe\SyliusKlarnaPlugin\Converter\PaymentConverter;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe_sylius_klarna.converter.payment', PaymentConverter::class)
        ->args([
            service('webgriffe_sylius_klarna.resolver.payment_country'),
            service('translator'),
            service('router'),
            service('liip_imagine.cache.manager'),
        ])
    ;

    $services->set('webgriffe_sylius_klarna.converter.hosted_payment_page', HostedPaymentPageConverter::class);

    $services->set('webgriffe_sylius_klarna.converter.order', OrderConverter::class)
        ->args([
            service('webgriffe_sylius_klarna.resolver.payment_country'),
        ])
    ;
};
