<?php

namespace Mitrik\Shipping\ServiceProviders\ServiceFlat;


use Exception;
use Mitrik\Shipping\Models\ShippingBox;
use Mitrik\Shipping\Models\ShippingService;
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxEmpty;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxOverweight;
use Mitrik\Shipping\ServiceProviders\Exceptions\PriceNotFound;
use Mitrik\Shipping\ServiceProviders\ServiceProvider;
use Mitrik\Shipping\ServiceProviders\ServiceProviderRate\ServiceProviderRate;
use Mitrik\Shipping\ServiceProviders\ServiceProviderRate\ServiceProviderRateCollection;
use Mitrik\Shipping\ServiceProviders\ServiceProviderService\ServiceProviderService;
use Mitrik\Shipping\ServiceProviders\ServiceProviderShipment\ServiceProviderShipmentCollection;
use Mitrik\Shipping\ServiceProviders\ServiceProviderShipment\ServiceProviderShipmentCustomsValue;
use Mitrik\Shipping\ServiceProviders\ShipFrom\ShipFrom;
use Mitrik\Shipping\ServiceProviders\ShipTo\ShipTo;

class ServiceFlat extends ServiceProvider
{
    /**
     *
     */
    private const NAME = 'Flat';

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * @return string[]
     */
    public static function serviceCodes(): array
    {
        return [
            'INTERNAL.FREE.PICKUP' => 'Free Pickup',
            'INTERNAL.FLAT' => 'Flat Shipping'
        ];
    }

    /**
     * @return array
     */
    public static function credentialKeys(): array
    {
        return [];
    }

    /**
     * @param Address $addressFrom
     * @param Address $addressTo
     * @param BoxCollection $boxes
     * @return ServiceProviderRateCollection
     * @throws BoxEmpty
     * @throws BoxOverweight|PriceNotFound
     */
    public function rates(Address $addressFrom, Address $addressTo, BoxCollection $boxes): ServiceProviderRateCollection
    {
        $this->checkForEmptyBoxes($boxes);
        $this->checkForOverweightBoxes($boxes);

        /** @var ServiceProviderRateCollection|ServiceProviderRate[] $rates */
        $rates = new ServiceProviderRateCollection();

        $boxRates = $this->rate($addressFrom, $addressTo, $boxes);

        foreach ($boxRates as $boxRate) {
            $found = false;

            foreach ($rates as $rate) {
                if ($rate->serviceProviderService()->serviceCode() == $boxRate->serviceCode()) {
                    $rate->setPrice($rate->price() + $boxRate->price());
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $rates[] = $boxRate;
            }
        }

        return $rates;
    }

    /**
     * @param Address $addressFrom
     * @param Address $addressTo
     * @param BoxCollection $boxes
     * @param ServiceProviderService|null $serviceProviderService
     * @return ServiceProviderRateCollection
     * @throws BoxEmpty
     * @throws BoxOverweight
     * @throws PriceNotFound
     */
    public function rate(Address $addressFrom, Address $addressTo, BoxCollection $boxes, ServiceProviderService|null $serviceProviderService = null): ServiceProviderRateCollection
    {
        $this->checkForEmptyBoxes($boxes);
        $this->checkForOverweightBoxes($boxes);

        /** @var ServiceProviderRateCollection|ServiceProviderRate[] $rates */
        $results = new ServiceProviderRateCollection();

        foreach (self::serviceCodes() as $serviceCode => $serviceName) {
            if ($serviceProviderService !== null) {
                if ($serviceProviderService->serviceCode() !== $serviceCode) {
                    continue;
                }
            }

            foreach ($boxes as $box) {
                $modelShippingBox = ShippingBox
                    ::where('length', $box->length())
                    ->where('width', $box->width())
                    ->where('height', $box->height())
                    ->first();

                if ($modelShippingBox === null) {
                    throw new Exception('Box ' . $box . ' not found');
                }

                if ($box->weight() > $modelShippingBox->max_weight) {
                    throw new BoxOverweight("Maximum weight for box " . $box . " is " . $modelShippingBox->max_weight);
                }

                $modelShippingService = ShippingService::whereName(self::NAME)->first();
                $modelShippingOption = $modelShippingService->shippingOptions()->where('code', $serviceCode)->first();

                $modelShippingOptionPrice = $modelShippingOption->shippingOptionPrices()->where('shipping_box_id', $modelShippingBox->id)->first();

                if ($modelShippingOptionPrice !== null) {
                    $serviceProviderService = new ServiceProviderService($serviceCode, $serviceName);
                    $results->addServicePrice(new ServiceProviderRate($serviceProviderService, $modelShippingOptionPrice->price));
                }
            }
        }

        if ($serviceProviderService !== null && $results->count() === 0) {
            throw new PriceNotFound('Price not found for service ' . $serviceProviderService->serviceName());
        }

        return $results;
    }

    public function ship(ShipFrom $shipFrom, ShipTo $shipTo, BoxCollection $boxes, ServiceProviderService $serviceProviderService, ServiceProviderShipmentCustomsValue|null $serviceProviderShipmentCustomsValue = null, $customData = []): ServiceProviderShipmentCollection
    {
        throw new \Exception('Not implemented yet.');
    }

}
