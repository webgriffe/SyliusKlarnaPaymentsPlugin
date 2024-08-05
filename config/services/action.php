<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Action\Api\CreateHostedPaymentPageSessionAction;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Action\Api\CreateOrderAction;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Action\Api\CreatePaymentSessionAction;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Action\Api\ReadHostedPaymentPageSessionAction;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Action\Api\ReadOrderAction;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Action\Api\ReadPaymentSessionAction;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Action\CancelAction;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Action\CaptureAction;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Action\ConvertSyliusPaymentToKlarnaHostedPaymentPageAction;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Action\ConvertSyliusPaymentToKlarnaOrderAction;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Action\ConvertSyliusPaymentToKlarnaPaymentAction;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Action\NotifyAction;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\Action\StatusAction;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Payum\KlarnaPaymentsApi;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe_sylius_klarna_payments.payum.action.capture', CaptureAction::class)
        ->public()
        ->args([
            service('webgriffe_sylius_klarna_payments.client'),
            service('webgriffe_sylius_klarna_payments.logger'),
            service('router'),
            service('request_stack'),
        ])
        ->tag('payum.action', ['factory' => KlarnaPaymentsApi::CODE, 'alias' => 'payum.action.capture'])
    ;

    $services->set('webgriffe_sylius_klarna_payments.payum.action.status', StatusAction::class)
        ->public()
        ->args([
            service('webgriffe_sylius_klarna_payments.logger'),
        ])
    ;

    $services->set('webgriffe_sylius_klarna_payments.payum.action.cancel', CancelAction::class)
        ->public()
        ->args([
            service('webgriffe_sylius_klarna_payments.logger'),
            service('request_stack'),
            service('router'),
        ])
        ->tag('payum.action', ['factory' => KlarnaPaymentsApi::CODE, 'alias' => 'payum.action.cancel'])
    ;

    $services->set('webgriffe_sylius_klarna_payments.payum.action.notify', NotifyAction::class)
        ->public()
        ->args([
            service('webgriffe_sylius_klarna_payments.logger'),
        ])
        ->tag('payum.action', ['factory' => KlarnaPaymentsApi::CODE, 'alias' => 'payum.action.notify'])
    ;

    $services->set('webgriffe_sylius_klarna_payments.payum.action.convert_sylius_payment_to_klarna_payment', ConvertSyliusPaymentToKlarnaPaymentAction::class)
        ->public()
        ->args([
            service('webgriffe_sylius_klarna_payments.converter.payment'),
        ])
        ->tag('payum.action', ['factory' => KlarnaPaymentsApi::CODE, 'alias' => 'payum.action.convert_sylius_payment_to_klarna_payment'])
    ;

    $services->set('webgriffe_sylius_klarna_payments.payum.action.convert_sylius_payment_to_klarna_hosted_payment_page', ConvertSyliusPaymentToKlarnaHostedPaymentPageAction::class)
        ->public()
        ->args([
            service('webgriffe_sylius_klarna_payments.converter.hosted_payment_page'),
        ])
        ->tag('payum.action', ['factory' => KlarnaPaymentsApi::CODE, 'alias' => 'payum.action.convert_sylius_payment_to_klarna_hosted_payment_page'])
    ;

    $services->set('webgriffe_sylius_klarna_payments.payum.action.convert_sylius_payment_to_klarna_order', ConvertSyliusPaymentToKlarnaOrderAction::class)
        ->public()
        ->args([
            service('webgriffe_sylius_klarna_payments.converter.order'),
        ])
        ->tag('payum.action', ['factory' => KlarnaPaymentsApi::CODE, 'alias' => 'payum.action.convert_sylius_payment_to_klarna_order'])
    ;

    $services->set('webgriffe_sylius_klarna_payments.payum.action.api.create_payment_session', CreatePaymentSessionAction::class)
        ->public()
        ->args([
            service('webgriffe_sylius_klarna_payments.client'),
        ])
        ->tag('payum.action', ['factory' => KlarnaPaymentsApi::CODE, 'alias' => 'payum.action.api.create_payment_session'])
    ;

    $services->set('webgriffe_sylius_klarna_payments.payum.action.api.read_payment_session', ReadPaymentSessionAction::class)
        ->public()
        ->args([
            service('webgriffe_sylius_klarna_payments.client'),
        ])
        ->tag('payum.action', ['factory' => KlarnaPaymentsApi::CODE, 'alias' => 'payum.action.api.read_payment_session'])
    ;

    $services->set('webgriffe_sylius_klarna_payments.payum.action.api.create_hosted_payment_page_session', CreateHostedPaymentPageSessionAction::class)
        ->public()
        ->args([
            service('webgriffe_sylius_klarna_payments.client'),
        ])
        ->tag('payum.action', ['factory' => KlarnaPaymentsApi::CODE, 'alias' => 'payum.action.api.create_hosted_payment_page_session'])
    ;

    $services->set('webgriffe_sylius_klarna_payments.payum.action.api.read_hosted_payment_page_session', ReadHostedPaymentPageSessionAction::class)
        ->public()
        ->args([
            service('webgriffe_sylius_klarna_payments.client'),
        ])
        ->tag('payum.action', ['factory' => KlarnaPaymentsApi::CODE, 'alias' => 'payum.action.api.read_hosted_payment_page_session'])
    ;

    $services->set('webgriffe_sylius_klarna_payments.payum.action.api.create_order', CreateOrderAction::class)
        ->public()
        ->args([
            service('webgriffe_sylius_klarna_payments.client'),
        ])
        ->tag('payum.action', ['factory' => KlarnaPaymentsApi::CODE, 'alias' => 'payum.action.api.create_order'])
    ;

    $services->set('webgriffe_sylius_klarna_payments.payum.action.api.read_order', ReadOrderAction::class)
        ->public()
        ->args([
            service('webgriffe_sylius_klarna_payments.client'),
        ])
        ->tag('payum.action', ['factory' => KlarnaPaymentsApi::CODE, 'alias' => 'payum.action.api.read_order'])
    ;
};
