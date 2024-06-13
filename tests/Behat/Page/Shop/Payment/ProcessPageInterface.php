<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusKlarnaPlugin\Behat\Page\Shop\Payment;

interface ProcessPageInterface
{
    public function waitForRedirect(): void;
}
