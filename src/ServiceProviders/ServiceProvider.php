<?php

namespace Mitrik\Shipping\ServiceProviders;

use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxInterface;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxEmpty;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxOverweight;
use Mitrik\Shipping\ServiceProviders\Exceptions\CustomsDeclarationMissing;
use Mitrik\Shipping\ServiceProviders\ServiceProviderRate\ServiceProviderRateCollection;
use Mitrik\Shipping\ServiceProviders\ServiceProviderService\ServiceProviderService;
use Mitrik\Shipping\ServiceProviders\ServiceProviderShipment\ServiceProviderShipment;
use Mitrik\Shipping\ServiceProviders\ServiceProviderShipment\ServiceProviderShipmentCollection;
use Mitrik\Shipping\ServiceProviders\ServiceProviderShipment\ServiceProviderShipmentCustomsValue;
use Mitrik\Shipping\ServiceProviders\ServiceProviderShipment\ShippingProviderShipment;
use Mitrik\Shipping\ServiceProviders\ShipFrom\ShipFrom;
use Mitrik\Shipping\ServiceProviders\ShipTo\ShipTo;

/**
 *
 */
abstract class ServiceProvider
{
    /**
     * @return array
     */
    abstract static function serviceCodes(): array;

    /**
     * @return array
     */
    abstract static function credentialKeys(): array;

    /**
     * @param Address $addressFrom
     * @param Address $addressTo
     * @param BoxCollection $boxes
     * @return ServiceProviderRateCollection
     */
    abstract function rates(Address $addressFrom, Address $addressTo, BoxCollection $boxes): ServiceProviderRateCollection;

    /**
     * @param Address $addressFrom
     * @param Address $addressTo
     * @param BoxCollection $boxes
     * @param ServiceProviderService|null $serviceProviderService
     * @return ServiceProviderRateCollection
     */
    abstract function rate(Address $addressFrom, Address $addressTo, BoxCollection $boxes, ServiceProviderService|null $serviceProviderService = null): ServiceProviderRateCollection;

    abstract function ship(ShipFrom $shipFrom, ShipTo $shipTo, BoxCollection $boxes, ServiceProviderService $serviceProviderService, ServiceProviderShipmentCustomsValue|null $serviceProviderShipmentCustomsValue = null, $customData = []): ServiceProviderShipmentCollection;

    /**
     * @param Box\BoxCollection $boxes
     * @return void
     * @throws Exceptions\BoxEmpty
     */
    public function checkForEmptyBoxes(BoxCollection $boxes): void
    {
        if ($boxes->isEmpty()) {
            throw new BoxEmpty("You must provide boxes in order to get shipment rates.");
        }
    }

    /**
     * @param Box\BoxCollection $boxes
     * @return void
     * @throws Exceptions\BoxOverweight
     */
    public function checkForOverweightBoxes(BoxCollection $boxes): void
    {
        /** @var BoxInterface $box */
        foreach ($boxes as $box) {
            if ($box->isOverweight()) {
                throw new BoxOverweight("Maximum weight for box " . $box . " is " . $box->maxWeight());
            }
        }
    }

    public function checkCustomsDeclaration(ShipFrom $shipFrom, ShipTo $shipTo, ServiceProviderShipmentCustomsValue|null $serviceProviderShipmentCustomsValue)
    {
        if ($shipFrom->address()->countryCodeIso2() !== $shipTo->address()->countryCodeIso2() && $serviceProviderShipmentCustomsValue === null) {
            throw new CustomsDeclarationMissing('Missing customs declaration.');
        }
    }
}
