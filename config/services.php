<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder;
use Payum\Klarna\Checkout\KlarnaCheckoutGatewayFactory;
use Webgriffe\SyliusKlarnaPlugin\Form\Type\SyliusKlarnaGatewayConfigurationType;
use Webgriffe\SyliusKlarnaPlugin\Payum\Action\ConvertPaymentAction;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe_sylius_klarna.gateway_factory_builder', GatewayFactoryBuilder::class)
        ->args([
            KlarnaCheckoutGatewayFactory::class,
        ])
        ->tag('payum.gateway_factory_builder', ['factory' => 'klarna_checkout'])
    ;

    $services->set('webgriffe_sylius_klarna.form.type.gateway_configuration', SyliusKlarnaGatewayConfigurationType::class)
        ->tag('sylius.gateway_configuration_type', ['type' => 'klarna_checkout', 'label' => 'Klarna Checkout'])
        ->tag('form.type')
    ;

    $services->set('webgriffe_sylius_klarna.payum.action.convert_payment', ConvertPaymentAction::class)
        ->public()
        ->tag('payum.action', ['factory' => 'klarna_checkout', 'alias' => 'payum.action.convert_payment'])
    ;

    $services->set('webgriffe_sylius_klarna.payum.action.convert_payment', ConvertPaymentAction::class)
        ->public()
        ->tag('payum.action', ['factory' => 'klarna_checkout', 'alias' => 'payum.action.convert_payment'])
    ;
};
