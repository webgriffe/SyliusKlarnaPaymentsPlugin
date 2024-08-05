<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Tests\Webgriffe\SyliusKlarnaPaymentsPlugin\Service\DummyClient;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe_sylius_klarna_payments.client', DummyClient::class)
        ->args([
            service('router'),
        ])
    ;
};
