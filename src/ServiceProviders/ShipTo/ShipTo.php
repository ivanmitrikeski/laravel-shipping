<?php

namespace Mitrik\Shipping\ServiceProviders\ShipTo;

use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Phone\Phone;

/**
 *
 */
class ShipTo
{
    /**
     * @param string $name
     * @param string $attentionName
     * @param Phone $phone
     * @param Address $address
     */
    public function __construct(
        private string  $name,
        private string  $attentionName,
        private Address $address,
        private Phone|null $phone = null,
        private string  $email = '',
        private string  $company = ''
    )
    {

    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): ShipTo
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function attentionName(): string
    {
        return $this->attentionName;
    }

    /**
     * @param string $attentionName
     * @return $this
     */
    public function setAttentionName(string $attentionName): ShipTo
    {
        $this->attentionName = $attentionName;
        return $this;
    }

    /**
     * @return Phone|null
     */
    public function phone(): Phone|null
    {
        return $this->phone;
    }

    /**
     * @param Phone|null $phone
     * @return ShipTo
     */
    public function setPhone(?Phone $phone): ShipTo
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return Address
     */
    public function address(): Address
    {
        return $this->address;
    }

    /**
     * @param Address $address
     * @return $this
     */
    public function setAddress(Address $address): ShipTo
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string
     */
    public function email(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): ShipTo
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function company(): string
    {
        return $this->company;
    }

    /**
     * @param string $company
     * @return ShipTo
     */
    public function setCompany(string $company): ShipTo
    {
        $this->company = $company;
        return $this;
    }

}
