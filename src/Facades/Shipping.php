<?php

namespace Mitrik\Shipping\Facades;

use Exception;
use Illuminate\Support\Facades\Facade;
use Mitrik\Shipping\Models\ShippingService;
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\ServiceProviderRate\ServiceProviderRateCollection;

class Shipping extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Shipping';
    }

    /**
     * @param Address $addressFrom
     * @param Address $addressTo
     * @param BoxCollection $boxes
     * @param array|null $exceptions
     * @return ServiceProviderRateCollection
     * @throws Exception
     */
    public function rates(Address $addressFrom, Address $addressTo, BoxCollection $boxes, array|null &$exceptions = null): ServiceProviderRateCollection
    {
        $collectionShippingService = ShippingService::query()->enabled()->get();

        $result = new ServiceProviderRateCollection();

        if ($exceptions !== null && !is_array($exceptions)) {
            $exceptions = [];
        }

        /** @var ShippingService $modelShippingService */
        foreach ($collectionShippingService as $modelShippingService) {
            $instance = $modelShippingService->instance();

            try {
                $instanceResults = $instance->rates($addressFrom, $addressTo, $boxes);

                if ($instanceResults->count() === 0) {
                    continue;
                }

                $result = $result->merge($instanceResults);
            } catch (Exception $e) {
                if ($exceptions !== null) {
                    $exceptions[] = $e;
                } else {
                    throw $e;
                }
            }
        }

        return $result;
    }
}

