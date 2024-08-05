<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusKlarnaPaymentsPlugin\Behat\Page\Shop\Payment;

interface ProcessPageInterface
{
    public function waitForRedirect(): void;
}
