<?php

namespace Mitrik\Shipping\ServiceProviders\ShipFrom;

use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Phone\Phone;

/**
 *
 */
class ShipFrom
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
        private Phone|null  $phone = null,
        private string  $email = '',
        private \DateTime  $shipDate = new \DateTime('tomorrow'),
        private string $company = '',
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
    public function setName(string $name): ShipFrom
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
    public function setAttentionName(string $attentionName): ShipFrom
    {
        $this->attentionName = $attentionName;
        return $this;
    }

    /**
     * @return Phone
     */
    public function phone(): Phone
    {
        return $this->phone;
    }

    /**
     * @param Phone $phone
     * @return $this
     */
    public function setPhone(Phone $phone): ShipFrom
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
    public function setAddress(Address $address): ShipFrom
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
    public function setEmail(string $email): ShipFrom
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function shipDate(): \DateTime
    {
        return $this->shipDate;
    }

    /**
     * @param \DateTime $shipDate
     * @return ShipFrom
     */
    public function setShipDate(\DateTime $shipDate): ShipFrom
    {
        $this->shipDate = $shipDate;
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
     * @return ShipFrom
     */
    public function setCompany(string $company): ShipFrom
    {
        $this->company = $company;
        return $this;
    }

}
