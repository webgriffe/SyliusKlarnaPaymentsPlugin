<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPlugin\Client\ValueObject;

final readonly class B2BAddress extends Address
{
    public function __construct(
        ?string $city = null,
        ?string $country = null,
        ?string $email = null,
        ?string $familyName = null,
        ?string $givenName = null,
        ?string $phone = null,
        ?string $postalCode = null,
        ?string $region = null,
        ?string $streetAddress = null,
        ?string $streetAddress2 = null,
        ?string $title = null,
        private ?string $attention = null,
        private ?string $organizationName = null,
    ) {
        parent::__construct(
            $city,
            $country,
            $email,
            $familyName,
            $givenName,
            $phone,
            $postalCode,
            $region,
            $streetAddress,
            $streetAddress2,
            $title,
        );
    }

    public function getAttention(): ?string
    {
        return $this->attention;
    }

    public function getOrganizationName(): ?string
    {
        return $this->organizationName;
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'attention' => $this->getAttention(),
            'city' => $this->getCity(),
            'country' => $this->getCountry(),
            'email' => $this->getEmail(),
            'family_name' => $this->getFamilyName(),
            'given_name' => $this->getGivenName(),
            'organization_name' => $this->getOrganizationName(),
            'phone' => $this->getPhone(),
            'postal_code' => $this->getPostalCode(),
            'region' => $this->getRegion(),
            'street_address' => $this->getStreetAddress(),
            'street_address2' => $this->getStreetAddress2(),
            'title' => $this->getTitle(),
        ], static fn ($value) => $value !== null);
    }
}
