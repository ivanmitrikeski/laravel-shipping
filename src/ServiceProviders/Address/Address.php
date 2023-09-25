<?php

namespace Mitrik\Shipping\ServiceProviders\Address;

/**
 * Address class used to calculate shipping and create shipments
 */
class Address
{
    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $companyName
     * @param string $line1
     * @param string $line2
     * @param string $city
     * @param string $postalCode
     * @param string $stateCodeIso2
     * @param string $countryCodeIso2
     */
    public function __construct(
        private string $firstName,
        private string $lastName,
        private string $companyName,
        private string $line1,
        private string $line2,
        private string $city,
        private string $postalCode,
        private string $stateCodeIso2,
        private string $countryCodeIso2
    )
    {
        $this->postalCode = str_replace(' ', '', $postalCode);
    }

    /**
     * @return string
     */
    public function firstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function lastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function companyName(): string
    {
        return $this->companyName;
    }

    /**
     * @param string $companyName
     */
    public function setCompanyName(string $companyName): void
    {
        $this->companyName = $companyName;
    }

    /**
     * @return string
     */
    public function line1(): string
    {
        return $this->line1;
    }

    /**
     * @param string $line1
     */
    public function setLine1(string $line1): void
    {
        $this->line1 = $line1;
    }

    /**
     * @return string
     */
    public function line2(): string
    {
        return $this->line2;
    }

    /**
     * @param string $line2
     */
    public function setLine2(string $line2): void
    {
        $this->line2 = $line2;
    }

    /**
     * @return string
     */
    public function city(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function postalCode(): string
    {
        return str_replace(' ', '', $this->postalCode);
    }

    /**
     * @param string $postalCode
     */
    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return string
     */
    public function stateCodeIso2(): string
    {
        return $this->stateCodeIso2;
    }

    /**
     * @param string $stateCodeIso2
     */
    public function setStateCodeIso2(string $stateCodeIso2): void
    {
        $this->stateCodeIso2 = $stateCodeIso2;
    }

    /**
     * @return string
     */
    public function countryCodeIso2(): string
    {
        return $this->countryCodeIso2;
    }

    /**
     * @param string $countryCodeIso2
     */
    public function setCountryCodeIso2(string $countryCodeIso2): void
    {
        $this->countryCodeIso2 = $countryCodeIso2;
    }

    /**
     * @return string
     */
    public function fullName(): string
    {
        return trim($this->firstName() . ' ' . $this->lastName());
    }

}
