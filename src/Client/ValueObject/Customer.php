<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

use DateTimeInterface;
use JsonSerializable;

readonly class Customer implements JsonSerializable
{
    public function __construct(
        private ?DateTimeInterface $dateOfBirth = null,
        private ?bool $isMale = null,
        private ?string $lastFourSsn = null,
        private ?string $nationalIdentificationNumber = null,
        private ?string $title = null,
    ) {
    }

    public function getDateOfBirth(): ?DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function getGender(): ?string
    {
        if ($this->isMale === null) {
            return null;
        }

        return $this->isMale ? 'male' : 'female';
    }

    public function getLastFourSsn(): ?string
    {
        return $this->lastFourSsn;
    }

    public function getNationalIdentificationNumber(): ?string
    {
        return $this->nationalIdentificationNumber;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getType(): string
    {
        return 'person';
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'date_of_birth' => $this->getDateOfBirth()?->format('Y-m-d'),
            'gender' => $this->getGender(),
            'last_four_ssn' => $this->getLastFourSsn(),
            'national_identification_number' => $this->getNationalIdentificationNumber(),
            'title' => $this->getTitle(),
            'type' => $this->getType(),
        ], static fn ($value) => $value !== null);
    }
}
