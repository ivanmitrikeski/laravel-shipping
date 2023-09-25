<?php

namespace Mitrik\Shipping\ServiceProviders\ServiceProviderRate;

use Mitrik\Shipping\ServiceProviders\ServiceProviderService\ServiceProviderService;

class ServiceProviderRate
{
    /**
     * @var ServiceProviderService|null
     */
    private ServiceProviderService|null $serviceProviderService = null;
    /**
     * @var float
     */
    private float $price = 0.00;
    /**
     * @var mixed|array
     */
    private mixed $metaData = [];

    /**
     * @param ServiceProviderService $serviceProviderService
     * @param $price
     * @param array $metaData
     */
    public function __construct(ServiceProviderService $serviceProviderService, $price, array $metaData = [])
    {
        $this->serviceProviderService = $serviceProviderService;
        $this->price = $price;
        $this->metaData = $metaData;
    }

    /**
     * @return array|mixed
     */
    public function metaData(): mixed
    {
        return $this->metaData;
    }

    /**
     * @return float
     */
    public function price(): float
    {
        return $this->price;
    }

    /**
     * @return ServiceProviderService|null
     */
    public function serviceProviderService(): ?ServiceProviderService
    {
        return $this->serviceProviderService;
    }

    /**
     * @param ServiceProviderService $serviceProviderService
     */
    public function setServiceProviderService(ServiceProviderService $serviceProviderService): void
    {
        $this->serviceProviderService = $serviceProviderService;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @param array|mixed $metaData
     */
    public function setMetaData(mixed $metaData): void
    {
        $this->metaData = $metaData;
    }

}
