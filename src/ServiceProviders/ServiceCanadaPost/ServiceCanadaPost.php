<?php

namespace Mitrik\Shipping\ServiceProviders\ServiceCanadaPost;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use LSS\Array2XML;
use LSS\XML2Array;
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxInterface;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxEmpty;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxOverweight;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidCredentials;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidShipmentParameters;
use Mitrik\Shipping\ServiceProviders\Exceptions\PriceNotFound;
use Mitrik\Shipping\ServiceProviders\ServiceProvider;
use Mitrik\Shipping\ServiceProviders\ServiceProviderRate\ServiceProviderRate;
use Mitrik\Shipping\ServiceProviders\ServiceProviderRate\ServiceProviderRateCollection;
use Mitrik\Shipping\ServiceProviders\ServiceProviderService\ServiceProviderService;

class ServiceCanadaPost extends ServiceProvider
{
    /**
     * Service's name.
     */
    private const NAME = 'CanadaPost';

    /**
     * @param ServiceCanadaPostCredentials $credentials
     */
    public function __construct(
        private ServiceCanadaPostCredentials $credentials)
    {

    }

    /**
     * @return array
     */
    public static function credentialKeys(): array
    {
        return ServiceCanadaPostCredentials::credentialKeys();
    }

    /**
     * @return string[]
     */
    public static function serviceCodes(): array
    {
        return [
            'DOM.EP' => 'Expedited Parcel',
            'DOM.RP' => 'Regular Parcel',
            'DOM.PC' => 'Priority',
            'DOM.XP' => 'Xpresspost',
            'DOM.XP.CERT' => 'Xpresspost Certified',
            'DOM.LIB' => 'Library Materials',
            'USA.EP' => 'Expedited Parcel USA',
            'USA.PW.ENV' => 'Priority Worldwide Envelope USA',
            'USA.PW.PAK' => 'Priority Worldwide pak USA',
            'USA.PW.PARCEL' => 'Priority Worldwide Parcel USA',
            'USA.SP.AIR' => 'Small Packet USA Air',
            'USA.TP' => 'Tracked Packet – USA',
            'USA.TP.LVM' => 'Tracked Packet – USA (LVM) (large volume mailers)',
            'USA.XP' => 'Xpresspost USA',
            'INT.XP' => 'Xpresspost International',
            'INT.IP.AIR' => 'International Parcel Air',
            'INT.IP.SURF' => 'International Parcel Surface',
            'INT.PW.ENV' => 'Priority Worldwide Envelope Int’l',
            'INT.PW.PAK' => 'Priority Worldwide pak Int’l',
            'INT.PW.PARCEL' => 'Priority Worldwide parcel Int’l',
            'INT.SP.AIR' => 'Small Packet International Air',
            'INT.SP.SURF' => 'Small Packet International Surface',
            'INT.TP' => 'Tracked Packet – International',
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
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     * @throws PriceNotFound
     */
    public function rates(Address $addressFrom, Address $addressTo, BoxCollection $boxes): ServiceProviderRateCollection
    {
        $this->checkForEmptyBoxes($boxes);
        $this->checkForOverweightBoxes($boxes);

        return $this->rate($addressFrom, $addressTo, $boxes);
    }

    /**
     * @param Address $addressFrom
     * @param Address $addressTo
     * @param BoxCollection $boxes
     * @param ServiceProviderService|null $serviceProviderService
     * @return ServiceProviderRateCollection
     * @throws GuzzleException
     * @throws BoxEmpty
     * @throws BoxOverweight
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     * @throws PriceNotFound
     */
    public function rate(Address $addressFrom, Address $addressTo, BoxCollection $boxes, ServiceProviderService|null $serviceProviderService = null): ServiceProviderRateCollection
    {
        $this->checkForEmptyBoxes($boxes);
        $this->checkForOverweightBoxes($boxes);

        $results = new ServiceProviderRateCollection();

        /** @var BoxInterface $box */
        foreach ($boxes as $box) {
            // The weight of the parcel in kilograms. (99.999)
            $weight = round($box->weight(), 3);

            $dimensions = [
                round($box->length(), 1), round($box->width(), 1), round($box->height(), 1) // (3.1 digits e.g. 999.9 pattern)
            ];

            rsort($dimensions, SORT_NUMERIC);

            $body = [
                'customer-number' => $this->credentials->customerNumber(),
                'parcel-characteristics' => [
                    'weight' => $weight,
                    'dimensions' => [
                        'length' => $dimensions[0],
                        'width' => $dimensions[1],
                        'height' => $dimensions[2],
                    ],
                ],
                'origin-postal-code' => $addressFrom->postalCode(),
                'destination' => [],
            ];

            if ($addressTo->countryCodeIso2() == 'CA') {
                $body['destination']['domestic']['postal-code'] = $addressTo->postalCode();
            } elseif ($addressTo->countryCodeIso2() == 'US') {
                $body['destination']['united-states']['zip-code'] = $addressTo->postalCode();
            } else {
                $body['destination']['international']['country-code'] = $addressTo->countryCodeIso2();
                $body['destination']['international']['postal-code'] = $addressTo->postalCode();
            }

            // https://www.canadapost-postescanada.ca/info/mc/business/productsservices/developers/services/rating/getrates/default.jsf

            $xml = Array2XML::createXML('mailing-scenario', $body);
            $xml->documentElement->setAttribute(
                'xmlns',
                'http://www.canadapost.ca/ws/ship/rate-v4'
            );

            $xmlString = $xml->saveXML();

            $client = new Client();

            $url = $this->credentials->test() ? 'https://ct.soa-gw.canadapost.ca/rs/ship/price' : 'https://soa-gw.canadapost.ca/rs/ship/price';

            try {
                $response = $client->request('POST', $url, [
                    'auth' => [$this->credentials->username(), $this->credentials->password()],
                    'headers' => [
                        'Content-Type' => 'application/vnd.cpc.ship.rate-v4+xml',
                        'Accept' => 'application/vnd.cpc.ship.rate-v4+xml',
                    ],
                    'body' => $xmlString
                ]);
            } catch (ClientException $e) {
                if ($e->getCode() === 401) {
                    throw new InvalidCredentials('Invalid ' . self::NAME . ' credentials');
                }

                if ($e->getCode() === 400) {
                    $array = XML2Array::createArray($e->getResponse()->getBody()->getContents());

                    throw new InvalidShipmentParameters($array['messages']['message']['description'] ?? 'Invalid Shipment Parameters');
                }

                throw $e;
            }

            $data = $response->getBody()->getContents();

            $array = XML2Array::createArray($data);

            if (isset($array['price-quotes'])) {
                foreach ($array['price-quotes']['price-quote'] as $priceQuote) {
                    if ($serviceProviderService !== null) {
                        if ($serviceProviderService->serviceCode() !== $priceQuote['service-code']) {
                            continue;
                        }
                    }

                    $serviceProviderServiceItem = new ServiceProviderService($priceQuote['service-code'], $priceQuote['service-name']);
                    $serviceProviderRate = new ServiceProviderRate($serviceProviderServiceItem, $priceQuote['price-details']['due'], (array) $priceQuote);
                    $results->addServicePrice($serviceProviderRate);
                }
            }
        }

        if ($serviceProviderService !== null && $results->count() === 0) {
            throw new PriceNotFound('Price not found for service ' . $serviceProviderService->serviceName());
        }

        return $results;
    }

}
