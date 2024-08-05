<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Payum;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\ServerRegion;

final class KlarnaPaymentsGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => KlarnaPaymentsApi::CODE,
            'payum.factory_title' => 'Klarna Payments',
            'payum.action.status' => '@webgriffe_sylius_klarna_payments.payum.action.status',
        ]);

        if (false === (bool) $config['payum.api']) {
            $defaultOptions = ['sandbox' => true, 'server_region' => ServerRegion::Europe->value];
            $config->defaults($defaultOptions);
            $config['payum.default_options'] = $defaultOptions;
            $config['payum.required_options'] = ['username', 'password', 'server_region', 'sandbox'];

            /**
             * @psalm-suppress MixedArgumentTypeCoercion
             *
             * @phpstan-ignore-next-line
             */
            $config['payum.api'] = static fn (ArrayObject $config): KlarnaPaymentsApi => new KlarnaPaymentsApi((array) $config);
        }
    }
}
