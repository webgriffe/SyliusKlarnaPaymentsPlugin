<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusKlarnaPlugin\Behat\Page\Shop\Payum\Capture;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

interface PayumCaptureDoPageInterface extends SymfonyPageInterface
{
    public function waitForRedirect(): void;
}
