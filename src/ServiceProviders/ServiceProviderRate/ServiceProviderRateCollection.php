<?php

namespace Mitrik\Shipping\ServiceProviders\ServiceProviderRate;

use Illuminate\Support\Collection;

class ServiceProviderRateCollection extends Collection
{
    /**
     * @param ServiceProviderRate $serviceProviderRate
     * @return $this
     */
    public function addServicePrice(ServiceProviderRate $serviceProviderRate): self
    {
        /** @var ServiceProviderRate $serviceProviderRateItem */
        foreach ($this->items as $serviceProviderRateItem) {
            if ($serviceProviderRateItem->serviceProviderService()->serviceCode() === $serviceProviderRate->serviceProviderService()->serviceCode()) {
                $serviceProviderRateItem->setPrice($serviceProviderRateItem->price() + $serviceProviderRate->price());
                return $this;
            }
        }

        $this->push($serviceProviderRate);
        return $this;
    }
}
