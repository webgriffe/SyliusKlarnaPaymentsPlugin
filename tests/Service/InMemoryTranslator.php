<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusKlarnaPaymentsPlugin\Service;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatorTrait;

final class InMemoryTranslator implements TranslatorInterface
{
    use TranslatorTrait;
}
