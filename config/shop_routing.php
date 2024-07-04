<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('@PayumBundle/Resources/config/routing/cancel.xml');

    $routes->add('webgriffe_sylius_klarna_plugin_payment_process', '/order/{tokenValue}/payment/process')
        ->controller(['webgriffe_sylius_klarna.controller.payment', 'processAction'])
        ->methods(['GET'])
    ;

    $routes->add('webgriffe_sylius_klarna_plugin_payment_status', '/payment/{paymentId}/status')
        ->controller(['webgriffe_sylius_klarna.controller.payment', 'statusAction'])
        ->methods(['GET'])
        ->requirements(['paymentId' => '\d+'])
    ;
};
