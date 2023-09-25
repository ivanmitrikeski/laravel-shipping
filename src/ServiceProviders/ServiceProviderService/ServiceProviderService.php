<?php

namespace Mitrik\Shipping\ServiceProviders\ServiceProviderService;

class ServiceProviderService
{
    /**
     * @var mixed
     */
    private mixed $serviceCode;
    /**
     * @var string
     */
    private string $serviceName;

    /**
     * @param $serviceCode
     * @param $serviceName
     */
    public function __construct($serviceCode, $serviceName)
    {
        $this->serviceCode = $serviceCode;
        $this->serviceName = $serviceName;
    }

    /**
     * @return mixed
     */
    public function serviceCode(): mixed
    {
        return $this->serviceCode;
    }

    /**
     * @param mixed $serviceCode
     * @return ServiceProviderService
     */
    public function setServiceCode(mixed $serviceCode): static
    {
        $this->serviceCode = $serviceCode;
        return $this;
    }

    /**
     * @return string
     */
    public function serviceName(): string
    {
        return $this->serviceName;
    }

    /**
     * @param string $serviceName
     * @return ServiceProviderService
     */
    public function setServiceName(string $serviceName): static
    {
        $this->serviceName = $serviceName;
        return $this;
    }

}
