<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusKlarnaPaymentsPlugin\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

final class InMemoryUrlGenerator implements UrlGeneratorInterface
{
    private RequestContext $context;

    public function __construct(
        RequestContext $context = null,
    ) {
        $this->context = $context ?? new RequestContext();
    }

    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }

    public function getContext(): RequestContext
    {
        return $this->context;
    }

    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        // TODO: Implement generate() method.
    }
}
