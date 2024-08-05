<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusKlarnaPaymentsPlugin\Converter\HostedPaymentPageConverter;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Converter\OrderConverter;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Converter\PaymentConverter;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe_sylius_klarna_payments.converter.payment', PaymentConverter::class)
        ->args([
            service('webgriffe_sylius_klarna_payments.resolver.payment_country'),
            service('translator'),
            service('router'),
            service('liip_imagine.cache.manager'),
        ])
    ;

    $services->set('webgriffe_sylius_klarna_payments.converter.hosted_payment_page', HostedPaymentPageConverter::class);

    $services->set('webgriffe_sylius_klarna_payments.converter.order', OrderConverter::class)
        ->args([
            service('webgriffe_sylius_klarna_payments.resolver.payment_country'),
            service('translator'),
            service('router'),
            service('liip_imagine.cache.manager'),
        ])
    ;
};
