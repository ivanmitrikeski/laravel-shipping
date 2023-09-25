<?php

namespace Mitrik\Shipping\ServiceProviders;

use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxInterface;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxEmpty;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxOverweight;
use Mitrik\Shipping\ServiceProviders\ServiceProviderRate\ServiceProviderRateCollection;
use Mitrik\Shipping\ServiceProviders\ServiceProviderService\ServiceProviderService;

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
}
