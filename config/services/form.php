<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusKlarnaPlugin\Form\Type\SyliusKlarnaPaymentsGatewayConfigurationType;
use Webgriffe\SyliusKlarnaPlugin\Payum\KlarnaPaymentsApi;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe_sylius_pagolight.form.type.gateway_configuration', SyliusKlarnaPaymentsGatewayConfigurationType::class)
        ->tag('sylius.gateway_configuration_type', ['type' => KlarnaPaymentsApi::CODE, 'label' => 'Klarna Payments'])
        ->tag('form.type')
    ;
};
