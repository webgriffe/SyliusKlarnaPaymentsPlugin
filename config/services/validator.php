<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusKlarnaPlugin\Validator\KlarnaPaymentMethodUniqueValidator;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe_sylius_klarna.validator.klarna_payment_method_unique', KlarnaPaymentMethodUniqueValidator::class)
        ->args([
            service('sylius.repository.payment_method'),
        ])
        ->tag('validator.constraint_validator')
    ;
};
