<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('webgriffe_sylius_klarna_payments_plugin_payment_process', '/order/{tokenValue}/payment/klarna-payments-process')
        ->controller(['webgriffe_sylius_klarna_payments.controller.payment', 'processAction'])
        ->methods(['GET'])
    ;
};
