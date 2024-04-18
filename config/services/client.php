<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use GuzzleHttp\Client as GuzzleHttpClient;
use Webgriffe\SyliusKlarnaPlugin\Client\Client;
use Webgriffe\SyliusKlarnaPlugin\Client\ClientInterface;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe_sylius_klarna.http_client', GuzzleHttpClient::class);

    $services->set('webgriffe_sylius_klarna.client', Client::class)
        ->args([
            service('webgriffe_sylius_klarna.http_client'),
            service('webgriffe_sylius_klarna.logger'),
        ])
    ;

    $services->alias(ClientInterface::class, 'webgriffe_sylius_klarna.client');
};
