<?php

namespace Mitrik\Shipping\ServiceProviders\ServiceProviderShipment;

class ServiceProviderShipment
{
    public function __construct(
        private string $trackingNumber,
        private string $shipmentLabelData,
        private string $shipmentLabelDataFormat,
        private array  $metaData,
    )
    {

    }

    public function trackingNumber(): string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(string $trackingNumber): ServiceProviderShipment
    {
        $this->trackingNumber = $trackingNumber;
        return $this;
    }

    public function shipmentLabelData(): string
    {
        return $this->shipmentLabelData;
    }

    public function setShipmentLabelData(string $shipmentLabelData): ServiceProviderShipment
    {
        $this->shipmentLabelData = $shipmentLabelData;
        return $this;
    }

    public function shipmentLabelDataFormat(): string
    {
        return $this->shipmentLabelDataFormat;
    }

    public function setShipmentLabelDataFormat(string $shipmentLabelDataFormat): ServiceProviderShipment
    {
        $this->shipmentLabelDataFormat = $shipmentLabelDataFormat;
        return $this;
    }

    public function metaData(): array
    {
        return $this->metaData;
    }

    public function setMetaData(array $metaData): ServiceProviderShipment
    {
        $this->metaData = $metaData;
        return $this;
    }

}
