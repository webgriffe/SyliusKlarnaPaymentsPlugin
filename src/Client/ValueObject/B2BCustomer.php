<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject;

use DateTimeInterface;
use Webgriffe\SyliusKlarnaPaymentsPlugin\Client\Enum\OrganizationEntityType;

final readonly class B2BCustomer extends Customer
{
    public function __construct(
        ?DateTimeInterface $dateOfBirth = null,
        ?bool $isMale = null,
        ?string $lastFourSsn = null,
        ?string $nationalIdentificationNumber = null,
        private ?OrganizationEntityType $organizationEntityType = null,
        private ?string $organizationRegistrationId = null,
        private ?string $vatId = null,
    ) {
        parent::__construct($dateOfBirth, $isMale, $lastFourSsn, $nationalIdentificationNumber);
    }

    public function getOrganizationEntityType(): ?OrganizationEntityType
    {
        return $this->organizationEntityType;
    }

    public function getType(): string
    {
        return 'organization';
    }

    public function getOrganizationRegistrationId(): ?string
    {
        return $this->organizationRegistrationId;
    }

    public function getVatId(): ?string
    {
        return $this->vatId;
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'date_of_birth' => $this->getDateOfBirth()?->format('Y-m-d'),
            'gender' => $this->getGender(),
            'last_four_ssn' => $this->getLastFourSsn(),
            'national_identification_number' => $this->getNationalIdentificationNumber(),
            'organization_entity_type' => $this->getOrganizationEntityType()?->value,
            'organization_registration_id' => $this->getOrganizationRegistrationId(),
            'title' => $this->getTitle(),
            'type' => $this->getType(),
            'vat_id' => $this->getVatId(),
        ], static fn ($value) => $value !== null);
    }
}
