<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Tests\Webgriffe\SyliusKlarnaPlugin\Behat\Context\Ui\KlarnaContext;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();
    $services->defaults()->public();

    $services->set('webgriffe_sylius_klarna.behat.context.ui.klarna', KlarnaContext::class)
        ->args([
            service('sylius.repository.payment_security_token'),
            service('sylius.repository.payment'),
            service('router'),
            service('behat.mink.default_session'),
            service('webgriffe_sylius_klarna.behat.page.shop.payment.process'),
            service('sylius.behat.page.shop.order.thank_you'),
            service('sylius.behat.page.shop.order.show'),
            service('sylius.repository.order'),
            service('sylius.behat.page.shop.order.show'),
        ])
    ;
};
