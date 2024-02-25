<?php

namespace Mitrik\Shipping\ServiceProviders\Phone;

/**
 *
 */
class Phone implements \Stringable
{
    /**
     * @param string $countryCode
     * @param string $phone
     * @param string $phoneExtension
     */
    public function __construct(
        private string  $countryCode,
        private string  $areaCode,
        private string  $number,
        private string  $extension = '',
    )
    {

    }

    /**
     * @return string
     */
    public function countryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     * @return Phone
     */
    public function setCountryCode(string $countryCode): Phone
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    /**
     * @return string
     */
    public function areaCode(): string
    {
        return $this->areaCode;
    }

    /**
     * @param string $areaCode
     * @return Phone
     */
    public function setAreaCode(string $areaCode): Phone
    {
        $this->areaCode = $areaCode;
        return $this;
    }

    /**
     * @return string
     */
    public function number(): string
    {
        return $this->number;
    }

    /**
     * @param string $number
     * @return Phone
     */
    public function setNumber(string $number): Phone
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return string
     */
    public function extension(): string
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     * @return Phone
     */
    public function setExtension(string $extension): Phone
    {
        $this->extension = $extension;
        return $this;
    }

    public function e164()
    {
        $result = ($this->countryCode !== '') ? ('+' . preg_replace("/[^0-9]/", '', $this->countryCode)) : '';
        $result.= ($this->areaCode !== '') ? (preg_replace("/[^0-9]/", '', $this->areaCode)) : '';
        $result.= preg_replace("/[^0-9]/", '', $this->number);

        return $result;
    }

    public function __toString()
    {
        return $this->e164();
    }
}
