<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Payum;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class KlarnaPaymentsGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => KlarnaPaymentsApi::CODE,
            'payum.factory_title' => 'Klarna Payments',
            'payum.action.status' => '@webgriffe_sylius_klarna.payum.action.status',
        ]);
    }
}
