<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Tests\Webgriffe\SyliusKlarnaPaymentsPlugin\Behat\Page\Shop\Payment\ProcessPage;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();
    $services->defaults()->public();

    $services->set('webgriffe_sylius_klarna.behat.page.shop.payment.process', ProcessPage::class)
        ->parent('sylius.behat.symfony_page')
    ;
};
