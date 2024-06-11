<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusKlarnaPlugin\MessageHandler\UpdatePaymentDetailsHandler;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe_sylius_klarna.message_handler.update_payment_details', UpdatePaymentDetailsHandler::class)
        ->tag('messenger.message_handler')
    ;
};
