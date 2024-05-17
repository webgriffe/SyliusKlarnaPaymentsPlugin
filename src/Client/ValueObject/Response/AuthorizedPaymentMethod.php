<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject\Response;

final readonly class AuthorizedPaymentMethod
{
    public function __construct(
        private int $numberOfDays,
        private int $numberOfInstallments,
        private string $type,
    ) {
    }

    public function getNumberOfDays(): int
    {
        return $this->numberOfDays;
    }

    public function getNumberOfInstallments(): int
    {
        return $this->numberOfInstallments;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
