<?php

declare(strict_types=1);

namespace Webgriffe\SyliusKlarnaPaymentsPlugin\Client\ValueObject;

use JsonSerializable;

readonly class Address implements JsonSerializable
{
    public function __construct(
        private ?string $city = null,
        private ?string $country = null,
        private ?string $email = null,
        private ?string $familyName = null,
        private ?string $givenName = null,
        private ?string $phone = null,
        private ?string $postalCode = null,
        private ?string $region = null,
        private ?string $streetAddress = null,
        private ?string $streetAddress2 = null,
        private ?string $title = null,
    ) {
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function getStreetAddress(): ?string
    {
        return $this->streetAddress;
    }

    public function getStreetAddress2(): ?string
    {
        return $this->streetAddress2;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function jsonSerialize(): array
    {
        return array_filter([
            'city' => $this->getCity(),
            'country' => $this->getCountry(),
            'email' => $this->getEmail(),
            'family_name' => $this->getFamilyName(),
            'given_name' => $this->getGivenName(),
            'phone' => $this->getPhone(),
            'postal_code' => $this->getPostalCode(),
            'region' => $this->getRegion(),
            'street_address' => $this->getStreetAddress(),
            'street_address2' => $this->getStreetAddress2(),
            'title' => $this->getTitle(),
        ], static fn ($value) => $value !== null);
    }
}
