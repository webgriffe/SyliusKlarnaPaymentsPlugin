<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use GuzzleHttp\Client as GuzzleHttpClient;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Client;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ClientInterface;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe_sylius_klarna_payments.http_client', GuzzleHttpClient::class);

    $services->set('webgriffe_sylius_klarna_payments.client', Client::class)
        ->args([
            service('webgriffe_sylius_klarna_payments.http_client'),
            service('webgriffe_sylius_klarna_payments.logger'),
        ])
    ;

    $services->alias(ClientInterface::class, 'webgriffe_sylius_klarna_payments.client');
};
