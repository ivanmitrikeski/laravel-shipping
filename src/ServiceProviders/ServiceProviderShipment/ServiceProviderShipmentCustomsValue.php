<?php

namespace Mitrik\Shipping\ServiceProviders\ServiceProviderShipment;

class ServiceProviderShipmentCustomsValue
{
    /**
     * @param float $amount
     * @param string $currency
     */
    public function __construct(
        private float  $amount,
        private string $currency,
        private array $customData = []
    )
    {

    }

    /**
     * @return float
     */
    public function amount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return ServiceProviderShipmentCustomsValue
     */
    public function setAmount(float $amount): ServiceProviderShipmentCustomsValue
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return ServiceProviderShipmentCustomsValue
     */
    public function setCurrency(string $currency): ServiceProviderShipmentCustomsValue
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return array
     */
    public function customData(): array
    {
        return $this->customData;
    }

    /**
     * @param array $customData
     * @return ServiceProviderShipmentCustomsValue
     */
    public function setCustomData(array $customData): ServiceProviderShipmentCustomsValue
    {
        $this->customData = $customData;
        return $this;
    }


}
