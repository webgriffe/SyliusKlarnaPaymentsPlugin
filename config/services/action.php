<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusKlarnaPlugin\Payum\Action\Api\CreatePaymentAction;
use Webgriffe\SyliusKlarnaPlugin\Payum\Action\CaptureAction;
use Webgriffe\SyliusKlarnaPlugin\Payum\Action\ConvertSyliusPaymentToKlarnaPaymentAction;
use Webgriffe\SyliusKlarnaPlugin\Payum\Action\StatusAction;
use Webgriffe\SyliusKlarnaPlugin\Payum\KlarnaPaymentsApi;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe_sylius_klarna.payum.action.capture', CaptureAction::class)
        ->public()
        ->args([
        ])
        ->tag('payum.action', ['factory' => KlarnaPaymentsApi::CODE, 'alias' => 'payum.action.capture'])
    ;

    $services->set('webgriffe_sylius_klarna.payum.action.status', StatusAction::class)
        ->public()
    ;

    $services->set('webgriffe_sylius_klarna.payum.action.convert_sylius_payment_to_klarna_payment', ConvertSyliusPaymentToKlarnaPaymentAction::class)
        ->public()
        ->args([
            service('webgriffe_sylius_klarna.converter.payment'),
        ])
        ->tag('payum.action', ['factory' => KlarnaPaymentsApi::CODE, 'alias' => 'payum.action.convert_sylius_payment_to_klarna_payment'])
    ;

    $services->set('webgriffe_sylius_klarna.payum.action.api.create_payment', CreatePaymentAction::class)
        ->public()
        ->args([
            service('webgriffe_sylius_klarna.client'),
        ])
        ->tag('payum.action', ['factory' => KlarnaPaymentsApi::CODE, 'alias' => 'payum.action.api.create_payment'])
    ;
};
