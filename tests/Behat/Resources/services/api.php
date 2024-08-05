<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Tests\Webgriffe\SyliusKlarnaPaymentsPlugin\Behat\Context\Api\KlarnaContext;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();
    $services->defaults()->public();

    $services->set('webgriffe_sylius_klarna.behat.context.api.klarna', KlarnaContext::class)
        ->args([
            service('sylius.repository.payment_security_token'),
            service('sylius.repository.payment'),
            service('router'),
            service('sylius.http_client'),
        ])
    ;
};
