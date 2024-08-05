<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusKlarnaPaymentsPlugin\Form\Type\SyliusKlarnaPaymentsGatewayConfigurationType;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApi;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe_sylius_klarna_payments.form.type.gateway_configuration', SyliusKlarnaPaymentsGatewayConfigurationType::class)
        ->tag('sylius.gateway_configuration_type', ['type' => KlarnaPaymentsApi::CODE, 'label' => 'Klarna Payments'])
        ->tag('form.type')
    ;
};
