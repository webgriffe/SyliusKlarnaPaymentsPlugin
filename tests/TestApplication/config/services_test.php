<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Tests\Webgriffe\SyliusKlarnaPaymentsPlugin\Service\DummyClient;

return static function (ContainerConfigurator $container) {
    if (str_starts_with($container->env(), 'test')) {
        $container->import('../../../vendor/sylius/sylius/src/Sylius/Behat/Resources/config/services.xml');
        $container->import('@WebgriffeSyliusKlarnaPaymentsPlugin/tests/Behat/Resources/services.php');
        $services = $container->services();

        $services->set('webgriffe_sylius_klarna_payments.client', DummyClient::class)
            ->args([
                service('router'),
            ])
        ;
    }
};
