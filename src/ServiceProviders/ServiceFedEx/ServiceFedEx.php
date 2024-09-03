<?php

namespace Mitrik\Shipping\ServiceProviders\ServiceFedEx;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\Box;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxEmpty;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxOverweight;
use Mitrik\Shipping\ServiceProviders\Exceptions\CustomsDeclarationMissing;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidCredentials;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidService;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidShipmentParameters;
use Mitrik\Shipping\ServiceProviders\Exceptions\PriceNotFound;
use Mitrik\Shipping\ServiceProviders\Exceptions\ShipmentNotCreated;
use Mitrik\Shipping\ServiceProviders\Measurement\Length;
use Mitrik\Shipping\ServiceProviders\Measurement\Weight;
use Mitrik\Shipping\ServiceProviders\ServiceProvider;
use Mitrik\Shipping\ServiceProviders\ServiceProviderRate\ServiceProviderRate;
use Mitrik\Shipping\ServiceProviders\ServiceProviderRate\ServiceProviderRateCollection;
use Mitrik\Shipping\ServiceProviders\ServiceProviderService\ServiceProviderService;
use Mitrik\Shipping\ServiceProviders\ServiceProviderShipment\ServiceProviderShipment;
use Mitrik\Shipping\ServiceProviders\ServiceProviderShipment\ServiceProviderShipmentCollection;
use Mitrik\Shipping\ServiceProviders\ServiceProviderShipment\ServiceProviderShipmentCustomsValue;
use Mitrik\Shipping\ServiceProviders\ShipFrom\ShipFrom;
use Mitrik\Shipping\ServiceProviders\ShipTo\ShipTo;

class ServiceFedEx extends ServiceProvider
{
    /**
     * Service's name.
     */
    private const NAME = 'FedEx';

    /**
     * @var ServiceFedExCredentials
     */
    private ServiceFedExCredentials $credentials;

    /**
     * @var string|null
     */
    private string|null $accessToken = null;
    /**
     * @var int|null
     */
    private int|null $accessTokenExpiresAt = null;

    /**
     * @param ServiceFedExCredentials $credentials
     */
    public function __construct(ServiceFedExCredentials $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * @return array
     */
    public static function credentialKeys(): array
    {
        return ServiceFedExCredentials::credentialKeys();
    }

    /**
     * @return array[]
     */
    public static function serviceCodes(): array
    {
        return [
            'Domestic' => [
                'FEDEX_GROUND' => 'FedEx Ground',
                'FEDEX_2_DAY' => 'FedEx 2 Day',
                'STANDARD_OVERNIGHT' => 'FedEx Standard Overnight',
                'PRIORITY_OVERNIGHT' => 'FedEx Priority Overnight',
                'FEDEX_EXPRESS_SAVER' => 'FedEx 3 Day',
                'FIRST_OVERNIGHT' => 'First Overnight',
                'FEDEX_2_DAY_AM' => '2 Day AM',
                'GROUND_HOME_DELIVERY' => 'Home Delivery'
            ],
            'International' => [
                'INTERNATIONAL_ECONOMY' => 'International Economy',
                'INTERNATIONAL_PRIORITY' => 'International Priority',
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
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     * @throws PriceNotFound
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
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     * @throws PriceNotFound
     * @throws GuzzleException
     * @throws BoxEmpty
     * @throws BoxOverweight
     */
    public function rate(Address $addressFrom, Address $addressTo, BoxCollection $boxes, ServiceProviderService|null $serviceProviderService = null): ServiceProviderRateCollection
    {
        $this->checkForEmptyBoxes($boxes);
        $this->checkForOverweightBoxes($boxes);

         $request = [
             "accountNumber" => [
                 "value" => $this->credentials->accountNumber()
             ],
             "rateRequestControlParameters" => [
                 "returnTransitTimes" => false,
                 "servicesNeededOnRateFailure" => true,
                 "variableOptions" => "FREIGHT_GUARANTEE",
                 "rateSortOrder" => "SERVICENAMETRADITIONAL"
             ],
             "requestedShipment" => [
                 "shipper" => [
                     "address" => [
                         "streetLines" => [
                             $addressFrom->line1(),
                             $addressFrom->line2()
                         ],
                         "city" => $addressFrom->city(),
                         "stateOrProvinceCode" => $addressFrom->stateCodeIso2(),
                         "postalCode" => $addressFrom->postalCode(),
                         "countryCode" => $addressFrom->countryCodeIso2(),
                         "residential" => false
                     ]
                 ],
                 "recipient" => [
                     "address" => [
                         "streetLines" => [
                             $addressTo->line1(),
                             $addressTo->line2()
                         ],
                         "city" => $addressTo->city(),
                         "stateOrProvinceCode" => $addressTo->stateCodeIso2(),
                         "postalCode" => $addressTo->postalCode(),
                         "countryCode" => $addressTo->countryCodeIso2(),
                         "residential" => false
                     ]
                 ],
                 "rateRequestType" => [
                     "ACCOUNT",
                     "LIST"
                 ],
                 "pickupType" => "DROPOFF_AT_FEDEX_LOCATION",
                 "requestedPackageLineItems" => [
                     [
                         "weight" => [
                             "units" => "LB",
                             "value" => 22
                         ],
                         "dimensions" => [
                             "length" => 10,
                             "width" => 8,
                             "height" => 2,
                             "units" => "IN"
                         ],
                     ]
                 ],
                 "packagingType" => "YOUR_PACKAGING",
                 "groupShipment" => true,
             ],
         ];

         $request['requestedShipment']['requestedPackageLineItems'] = [];

        /** @var Box $box */
        foreach ($boxes as $box) {
            $request['requestedShipment']['requestedPackageLineItems'][] = [
                "weight" => [
                    "units" => $box->unitOfMeasurementWeight() == Weight::KG ? 'KG' : 'LB',
                    "value" => $box->weight()
                ],
                "dimensions" => [
                    "length" => $box->length(),
                    "width" => $box->width(),
                    "height" => $box->height(),
                    "units" => $box->unitOfMeasurementSize() == Length::CM ? 'CM' : 'IN'
                ],
            ];

        }

        $client = new Client();

        $url = $this->credentials->test() ? 'https://apis-sandbox.fedex.com/rate/v1/rates/quotes' : 'https://apis.fedex.com/rate/v1/rates/quotes';

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token(),
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($request),
            ]);

            $results = new ServiceProviderRateCollection();

            $responseJson = json_decode($response->getBody()->getContents());

            if (isset($responseJson->output->rateReplyDetails)) {
                foreach ($responseJson->output->rateReplyDetails as $priceQuote) {
                    if ($serviceProviderService !== null) {
                        if ($serviceProviderService->serviceCode() !== $priceQuote->serviceType) {
                            continue;
                        }
                    }

                    if (!isset($priceQuote->ratedShipmentDetails)) {
                        continue;
                    }

                    $serviceProviderServiceItem = new ServiceProviderService($priceQuote->serviceType, $priceQuote->serviceName);
                    $serviceProviderRate = new ServiceProviderRate($serviceProviderServiceItem, $priceQuote->ratedShipmentDetails[0]->totalNetCharge, (array) $priceQuote);
                    $results->addServicePrice($serviceProviderRate);
                }
            }

            return $results;
        } catch (RequestException $e) {
            $code = $e->getCode();

            $jsonError = json_decode($e->getResponse()->getBody(), true);

            $code = $jsonError['errors'][0]['code'] ?? $code;
            $message = $jsonError['errors'][0]['message'] ?? $code ?? 'Invalid Shipment Parameters';

            throw match ($code) {
                401 => new InvalidCredentials('Invalid ' . self::NAME . ' credentials'),
                'SERVICE.PACKAGECOMBINATION.INVALID' => new InvalidShipmentParameters($message ?? 'Invalid Shipment Parameters'),
                default => $e,
            };
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'credentials were not valid')) {
                throw new InvalidCredentials('Invalid ' . self::NAME . ' credentials');
            }

            throw $e;
        }

        throw new PriceNotFound('Price not found.');
    }

    /**
     * @return string
     * @throws InvalidCredentials
     * @throws GuzzleException
     */
    public function token(): string
    {
        if ($this->accessToken !== null && $this->accessTokenExpiresAt > time()) {
            return $this->accessToken;
        }

        $client = new Client();

        $url = $this->credentials->test() ? 'https://apis-sandbox.fedex.com/oauth/token' : 'https://apis.fedex.com/oauth/token';

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->credentials->clientId(),
                    'client_secret' => $this->credentials->clientSecret(),
                ],
            ]);
        } catch (RequestException $e) {
            $code = $e->getCode();
            $jsonError = json_decode($e->getResponse()->getBody(), true);

            $code = $jsonError['errors'][0]['code'] ?? $code;
            $message = $jsonError['errors'][0]['message'] ?? $code ?? 'Invalid Shipment Parameters';

            throw match ($code) {
                401 => new InvalidCredentials('Invalid ' . self::NAME . ' credentials'),
                'FORBIDDEN.ERROR' => throw new InvalidCredentials($message),
                default => new InvalidShipmentParameters($message),
            };
        } catch (Exception $e) {
            throw $e;
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $accessToken = $data['access_token'] ?? '';
        $accessTokenExpiresAt = time() + $data['expires_in'] ?? 0;

        if ($accessToken === '') {
            throw new InvalidCredentials('Invalid ' . self::NAME . ' credentials');
        }

        $this->accessToken = $accessToken;
        $this->accessTokenExpiresAt = $accessTokenExpiresAt;

        return $accessToken;
    }

    public function ship(ShipFrom $shipFrom, ShipTo $shipTo, BoxCollection $boxes, ServiceProviderService $serviceProviderService, ServiceProviderShipmentCustomsValue|null $serviceProviderShipmentCustomsValue = null, $customData = []): ServiceProviderShipmentCollection
    {
        $this->checkForEmptyBoxes($boxes);
        $this->checkForOverweightBoxes($boxes);
        $this->checkCustomsDeclaration($shipFrom, $shipTo, $serviceProviderShipmentCustomsValue);

        $request = [
            "labelResponseOptions" => "LABEL",
            "requestedShipment" => [
                "shipper" => [
                    "contact" => [
                        "personName" => $shipFrom->name(),
                        "phoneNumber" => $shipFrom->phone() . '',
                        "phoneExtension" => $shipFrom->phone()->extension() . '',
                        "emailAddress" => $shipFrom->email(),
                        "company" => $shipFrom->company(),
                    ],
                    "address" => [
                        "streetLines" => [
                            $shipFrom->address()->line1(),
                            $shipFrom->address()->line2(),
                        ],
                        "city" => $shipFrom->address()->city(),
                        "stateOrProvinceCode" => $shipFrom->address()->stateCodeIso2(),
                        "postalCode" => $shipFrom->address()->postalCode(),
                        "countryCode" => $shipFrom->address()->countryCodeIso2(),
                    ],
                ],
                "recipients" => [
                    [
                        "contact" => [
                            "personName" => $shipTo->name(),
                            "phoneNumber" => $shipTo->phone() . '',
                            "emailAddress" => $shipFrom->email(),
                            "company" => $shipFrom->company(),
                        ],
                        "address" => [
                            "streetLines" => [
                                $shipTo->address()->line1(),
                                $shipTo->address()->line2(),
                            ],
                            "city" => $shipTo->address()->city(),
                            "stateOrProvinceCode" => $shipTo->address()->stateCodeIso2(),
                            "postalCode" => $shipTo->address()->postalCode(),
                            "countryCode" => $shipTo->address()->countryCodeIso2(),
                        ],
                    ],
                ],
                "shipDatestamp" => $shipFrom->shipDate()->format('Y-m-d'),
                "pickupType" => "USE_SCHEDULED_PICKUP",
                "serviceType" => $serviceProviderService->serviceCode(),
                "packagingType" => "YOUR_PACKAGING",
                "blockInsightVisibility" => false,
                "shippingChargesPayment" => [
                    "paymentType" => "SENDER"
                ],
                "labelSpecification" => [
                    "imageType" => "PNG",
                    "labelStockType" => "PAPER_4X6",
                ],
                "requestedPackageLineItems" => [[]],
            ],
            "accountNumber" => [
                "value" => $this->credentials->accountNumber()
            ],
        ];

        if ($serviceProviderShipmentCustomsValue !== null) {
            $request['requestedShipment']['customsClearanceDetail']['totalCustomsValue'] = [
                'amount' => $serviceProviderShipmentCustomsValue->amount(),
                'currency' => $serviceProviderShipmentCustomsValue->currency(),
            ];

            $request['requestedShipment']['customsClearanceDetail'] = $serviceProviderShipmentCustomsValue->customData() + $request['requestedShipment']['customsClearanceDetail'];
        }

        $request['requestedShipment']['requestedPackageLineItems'] = [];

        /** @var Box $box */
        foreach ($boxes as $box) {
            $request['requestedShipment']['requestedPackageLineItems'][] = [
                "weight" => [
                    "units" => $box->unitOfMeasurementWeight() == Weight::KG ? 'KG' : 'LB',
                    "value" => $box->weight()
                ],
                "dimensions" => [
                    "length" => (int) round($box->length()),
                    "width" => (int) round($box->width()),
                    "height" => (int) round($box->height()),
                    "units" => $box->unitOfMeasurementSize() == Length::CM ? 'CM' : 'IN'
                ],
            ];

        }

        $request = array_merge_recursive($customData, $request);

        $client = new Client();

        $url = $this->credentials->test() ? 'https://apis-sandbox.fedex.com/ship/v1/shipments' : 'https://apis.fedex.com/ship/v1/shipments';

        $results = new ServiceProviderShipmentCollection();

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token(),
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($request),
            ]);



            $responseJson = json_decode($response->getBody()->getContents(), true);

            $success = isset($responseJson['transactionId'], $responseJson['output']);

            if ($success) {
                foreach ($responseJson['output']['transactionShipments'][0]['pieceResponses'] as $pieceResponse) {
                    foreach ($pieceResponse['packageDocuments'] as $packageDocument) {
                        if ($packageDocument['contentType'] !== 'LABEL') {
                            continue;
                        }

                        $trackingNumber = $pieceResponse['trackingNumber'];
                        $shippingLabelFormat = $packageDocument['docType'];
                        $shippingLabelData = $packageDocument['encodedLabel'];

                        $results->push(new ServiceProviderShipment($trackingNumber, $shippingLabelData, $shippingLabelFormat, $pieceResponse));
                    }
                }
            }
        } catch (RequestException $e) {
            $code = $e->getCode();

            $jsonError = json_decode($e->getResponse()->getBody(), true);

            $code = $jsonError['errors'][0]['code'] ?? $code;
            $message = $jsonError['errors'][0]['message'] ?? $code ?? 'Invalid Shipment Parameters';

            throw match ($code) {
                401 => new InvalidCredentials('Invalid ' . self::NAME . ' credentials'),
                'SERVICE.PACKAGECOMBINATION.INVALID', 'INVALID.INPUT.EXCEPTION', 'REQUESTEDPACKAGELINEITEMS.WEIGHTSUNITS.INVALID' => new InvalidShipmentParameters($message ?? 'Invalid Shipment Parameters'),
                'SELECTED.DESTINATION.SERVICETYPE.INVALID' => new InvalidService($message ?? 'Invalid service selected between locations.'),
                default => $e,
            };
        } catch (Exception $e) {
            throw $e;
        }

        if ($results->isNotEmpty()) {
            return $results;
        }

        throw new ShipmentNotCreated('Unable to create shipment.');
    }


}
