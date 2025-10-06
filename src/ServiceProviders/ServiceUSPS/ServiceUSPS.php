<?php

namespace Mitrik\Shipping\ServiceProviders\ServiceUSPS;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Http;
use LSS\XML2Array;
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\Box;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxEmpty;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxOverweight;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidAddress;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidCredentials;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidShipmentParameters;
use Mitrik\Shipping\ServiceProviders\Exceptions\PriceNotFound;
use Mitrik\Shipping\ServiceProviders\Measurement\Length;
use Mitrik\Shipping\ServiceProviders\Measurement\Weight;
use Mitrik\Shipping\ServiceProviders\ServiceProvider;
use Mitrik\Shipping\ServiceProviders\ServiceProviderRate\ServiceProviderRate;
use Mitrik\Shipping\ServiceProviders\ServiceProviderRate\ServiceProviderRateCollection;
use Mitrik\Shipping\ServiceProviders\ServiceProviderService\ServiceProviderService;
use Mitrik\Shipping\ServiceProviders\ServiceProviderShipment\ServiceProviderShipmentCollection;
use Mitrik\Shipping\ServiceProviders\ServiceProviderShipment\ServiceProviderShipmentCustomsValue;
use Mitrik\Shipping\ServiceProviders\ShipFrom\ShipFrom;
use Mitrik\Shipping\ServiceProviders\ShipTo\ShipTo;

class ServiceUSPS extends ServiceProvider
{
    /**
     * Service's name.
     */
    private const NAME = 'USPS';

    /**
     * @var ServiceUSPSCredentials
     */
    private ServiceUSPSCredentials $credentials;

    /**
     * @param ServiceUSPSCredentials $credentials
     */
    public function __construct(ServiceUSPSCredentials $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * @return array
     */
    public static function credentialKeys(): array
    {
        return ServiceUSPSCredentials::credentialKeys();
    }

    /**
     * @return array[]
     */
    public static function serviceCodes(): array
    {
        return [
            'US' => [
                'PARCEL_SELECT'                        => 'Parcel Select',
                'PARCEL_SELECT_LIGHTWEIGHT'            => 'Parcel Select Lightweight',
                'USPS_CONNECT_LOCAL'                   => 'USPS Connect Local',
                'USPS_CONNECT_REGIONAL'                => 'USPS Connect Regional',
                'USPS_CONNECT_MAIL'                    => 'USPS Connect Mail',
                'USPS_GROUND_ADVANTAGE'                => 'USPS Ground Advantage',
                'USPS_GROUND_ADVANTAGE_RETURN_SERVICE' => 'USPS Ground Advantage Return Service',
                'PRIORITY_MAIL_EXPRESS'                => 'Priority Mail Express',
                'PRIORITY_MAIL_EXPRESS_RETURN_SERVICE' => 'Priority Mail Express Return Service',
                'PRIORITY_MAIL'                        => 'Priority Mail',
                'PRIORITY_MAIL_RETURN_SERVICE'         => 'Priority Mail Return Service',
                'FIRST-CLASS_PACKAGE_SERVICE'          => 'First-Class Package Service',
                'LIBRARY_MAIL'                         => 'Library Mail',
                'MEDIA_MAIL'                           => 'Media Mail',
                'BOUND_PRINTED_MATTER'                 => 'Bound Printed Matter',
                'ALL'                                  => 'All Domestic Services',
                'ALL_OUTBOUND'                         => 'All Outbound Services',
                'ALL_RETURNS'                          => 'All Return Services'
            ],
            'International' => [
                'FIRST-CLASS_PACKAGE_INTERNATIONAL_SERVICE' => 'First-Class Package International Service',
                'PRIORITY_MAIL_INTERNATIONAL'              => 'Priority Mail International',
                'PRIORITY_MAIL_EXPRESS_INTERNATIONAL'      => 'Priority Mail Express International',
                'GLOBAL_EXPRESS_GUARANTEED'                => 'Global Express Guaranteed',
                'ALL'                                      => 'All International Services'
            ]
        ];
    }

    /**
     * @param Address $addressFrom
     * @param Address $addressTo
     * @param BoxCollection $boxes
     * @return ServiceProviderRateCollection
     * @throws BoxEmpty
     * @throws BoxOverweight
     * @throws GuzzleException
     * @throws InvalidAddress
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     */
    public function rates(Address $addressFrom, Address $addressTo, BoxCollection $boxes): ServiceProviderRateCollection
    {
        return $this->rate($addressFrom, $addressTo, $boxes);
    }

    /**
     * @param Address $addressFrom
     * @param Address $addressTo
     * @param BoxCollection $boxes
     * @param ServiceProviderService|null $serviceProviderService
     * @return ServiceProviderRateCollection
     * @throws BoxEmpty
     * @throws BoxOverweight
     * @throws InvalidAddress
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     * @throws GuzzleException
     */
    public function rate(Address $addressFrom, Address $addressTo, BoxCollection $boxes, ServiceProviderService|null $serviceProviderService = null): ServiceProviderRateCollection
    {
        $this->checkForEmptyBoxes($boxes);
        $this->checkForOverweightBoxes($boxes);

        $results = new ServiceProviderRateCollection();

        $loginUrl = $this->credentials->test() ? 'https://apis-tem.usps.com/oauth2/v3/token' : 'https://apis.usps.com/oauth2/v3/token';
        $url = $this->credentials->test() ? 'https://apis-tem.usps.com/shipments/v3/options/search' : 'https://apis.usps.com/shipments/v3/options/search';

        $request = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->credentials->clientId(),
            'client_secret' => $this->credentials->clientSecret(),
        ];

        $response = Http::asForm()
            ->post($loginUrl, $request);

        $token = '';
        if ($response->successful()) {
            $data = $response->json();
            $token = $data['access_token'] ?? null;
        } else {
            throw new InvalidCredentials('Invalid ' . self::NAME . ' credentials');
        }

        if (empty($token)) {
            throw new InvalidCredentials('Invalid ' . self::NAME . ' credentials');
        }

        $packages = '';

        /** @var Box $box */
        foreach ($boxes as $boxId => $box) {

            $lbs = $box->weight();
            if ($box->unitOfMeasurementWeight() === Weight::KG) {
                $lbs *= 2.20462;
            }

            $length = $box->length();
            if ($box->unitOfMeasurementSize() === Length::CM) {
                $length *= 0.393701;
            }

            $width = $box->width();
            if ($box->unitOfMeasurementSize() === Length::CM) {
                $width *= 0.393701;
            }

            $height = $box->height();
            if ($box->unitOfMeasurementSize() === Length::CM) {
                $height *= 0.393701;
            }

            $request = [
                "pricingOptions" => [
                    ["priceType" => "RETAIL"]
                ],
                "originZIPCode" => $addressFrom->postalCode(),
                "destinationZIPCode" => $addressTo->postalCode(),
                "destinationEntryFacilityType" => "NONE",
                "packageDescription" => [
                    "weight" => $lbs,
                    "weightUnit" => "POUND",
                    "length" => $length,
                    "width" => $width,
                    "height" => $height,
                    "mailClass" => "ALL_OUTBOUND"
                ]
            ];

            if ($addressFrom->countryCodeIso2() !== 'US' || $addressTo->countryCodeIso2() !== 'US') {
                $request['destinationCountryCode'] = $addressTo->countryCodeIso2();
                $request['foreignPostalCode'] = $addressTo->postalCode();
                $request['packageDescription']['mailClass'] = 'ALL';
                unset($request['destinationZIPCode']);
            }

            $response = Http::withToken($token)
                ->acceptJson()
                ->post($url, $request);

            if ($response->successful()) {
                $pricingOptionsAll = $response->json('pricingOptions');
                if (count($pricingOptionsAll) === 0) {
                    continue;
                }

                foreach ($pricingOptionsAll as $pricingOptions) {
                    foreach ($pricingOptions['shippingOptions'] as $pricingOption) {
                        foreach ($pricingOption['rateOptions'] as $rateOption) {
                            foreach ($rateOption['rates'] as $rate) {
                                $serviceCode = $rate['mailClass'] . '-' . $rate['rateIndicator'];
                                $serviceName = $rate['productName'];
                                if ($serviceName === null) {
                                    continue;
                                }

                                $serviceProviderService = new ServiceProviderService($serviceCode, $serviceName);
                                $serviceProviderRate = new ServiceProviderRate($serviceProviderService, $rate['price'], (array)$rate);

                                $results->addServicePrice($serviceProviderRate);
                            }
                        }
                    }
                }

            } else {
                $error = $response->json('error.message', 'Invalid Shipment Parameters');
                if (str_contains($error, 'No valid rates for these parameters')) {
                    throw new PriceNotFound('Price not found.');
                }

                throw new InvalidShipmentParameters($error);
            }
        }

        if ($results->count() === 0) {
            throw new PriceNotFound('Price not found.');
        }
        return $results;
    }

    public function ship(ShipFrom $shipFrom, ShipTo $shipTo, BoxCollection $boxes, ServiceProviderService $serviceProviderService, ServiceProviderShipmentCustomsValue|null $serviceProviderShipmentCustomsValue = null, $customData = []): ServiceProviderShipmentCollection
    {
        throw new \Exception('Not implemented yet.');
    }
}
