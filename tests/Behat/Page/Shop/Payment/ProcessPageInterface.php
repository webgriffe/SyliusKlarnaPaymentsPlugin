<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusKlarnaPaymentsPlugin\Behat\Page\Shop\Payment;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

interface ProcessPageInterface extends SymfonyPageInterface
{
    public function waitForRedirect(): void;
}
