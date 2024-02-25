<?php

namespace Mitrik\Shipping\ServiceProviders\ServiceUPS;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\Box;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxEmpty;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxOverweight;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidCredentials;
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

class ServiceUPS extends ServiceProvider
{
    /**
     * Service's name.
     */
    private const NAME = 'UPS';

    /**
     * @var ServiceUPSCredentials
     */
    private ServiceUPSCredentials $credentials;

    /**
     * @var string|null
     */
    private string|null $accessToken = null;
    /**
     * @var int|null
     */
    private int|null $accessTokenExpiresAt = null;

    /**
     * @param ServiceUPSCredentials $credentials
     */
    public function __construct(ServiceUPSCredentials $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * @return array
     */
    public static function credentialKeys(): array
    {
        return ServiceUPSCredentials::credentialKeys();
    }

    /**
     * @return array[]
     */
    public static function serviceCodes(): array
    {
        return [
            'Domestic' => [
                '01' => 'UPS Next Day Air',
                '02' => 'UPS 2nd Day Air',
                '03' => 'UPS Ground',
                '04' => 'UPS Canada Express Saver',
                '12' => 'UPS 3 Day Select',
                '13' => 'UPS Next Day Air Saver',
                '14' => 'UPS Next Day Air Early',
                '15' => 'UPS United States Next Day Air Early A.M.',
                '17' => 'UPS Canada Expedited',
                '20' => 'UPS Canada Standard',
                '22' => 'UPS United States Ground – Returns Plus – Three Pickup Attempts',
                '32' => 'UPS United States Next Day Air Early A.M. – COD',
                '33' => 'UPS United States Next Day Air Early A.M. – Saturday Delivery, COD',
                '41' => 'UPS United States Next Day Air Early A.M. – Saturday Delivery',
                '42' => 'UPS United States Ground – Signature Required',
                '44' => 'the UPS United States Next Day Air – Saturday Delivery',
                '59' => 'UPS 2nd Day Air A.M.',
                '93' => 'UPS Sure Post',
                '66' => 'UPS United States Worldwide Express',
                '72' => 'UPS United States Ground – Collect on Delivery',
                '78' => 'UPS United States Ground – Returns Plus – One Pickup Attempt',
                '90' => 'UPS United States Ground – Returns – UPS Prints and Mails Label',
                'A0' => 'UPS United States Next Day Air Early A.M. – Adult Signature Required',
                'A1' => 'UPS United States Next Day Air Early A.M. – Saturday Delivery, Adult Signature Required',
                'A2' => 'UPS United States Next Day Air – Adult Signature Required',
                'A8' => 'UPS United States Ground – Adult Signature Required',
                'A9' => 'UPS United States Next Day Air Early A.M. – Adult Signature Required, COD',
                'AA' => 'UPS United States Next Day Air Early A.M. – Saturday Delivery, Adult Signature Required, COD'
            ],
            'International' => [
                '07' => 'UPS Worldwide Express',
                '08' => 'UPS Worldwide Expedited',
                '11' => 'UPS Standard',
                '54' => 'UPS Worldwide Express Plus',
                '65' => 'UPS Saver',
                '82' => 'UPS Today Standard',
                '83' => 'UPS Today Dedicated Courier',
                '84' => 'UPS Today Intercity',
                '85' => 'UPS Today Express',
                '86' => 'UPS Today Express Saver',
                '96' => 'UPS Worldwide Express Freight',
                '70' => 'UPS Access Point Economy'
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
            "RateRequest" => [
                "Request" => [
                    "TransactionReference" => [
                        "CustomerContext" => "CustomerContext",
                        "TransactionIdentifier" => "TransactionIdentifier"
                    ]
                ],
                "Shipment" => [
                    "Shipper" => [
                        "Name" => ($addressFrom->companyName() != '') ? $addressFrom->companyName() : $addressFrom->fullName(),
                        "ShipperNumber" => $this->credentials->accountNumber(),
                        "Address" => [
                            "AddressLine" => [
                                $addressFrom->line1(),
                                $addressFrom->line2(),
                            ],
                            "City" => $addressFrom->city(),
                            "StateProvinceCode" => $addressFrom->stateCodeIso2(),
                            "PostalCode" => $addressFrom->postalCode(),
                            "CountryCode" => $addressFrom->countryCodeIso2()
                        ]
                    ],
                    "ShipTo" => [
                        "Name" => ($addressTo->companyName() != '') ? $addressTo->companyName() : $addressTo->fullName(),
                        "Address" => [
                            "AddressLine" => [
                                $addressTo->line1(),
                                $addressTo->line2(),
                            ],
                            "City" => $addressTo->city(),
                            "StateProvinceCode" => $addressTo->stateCodeIso2(),
                            "PostalCode" => $addressTo->postalCode(),
                            "CountryCode" => $addressTo->countryCodeIso2()
                        ]
                    ],
                    "ShipFrom" => [
                        "Name" => ($addressFrom->companyName() != '') ? $addressFrom->companyName() : $addressFrom->fullName(),
                        "Address" => [
                            "AddressLine" => [
                                $addressFrom->line1(),
                                $addressFrom->line2(),
                            ],
                            "City" => $addressFrom->city(),
                            "StateProvinceCode" => $addressFrom->stateCodeIso2(),
                            "PostalCode" => $addressFrom->postalCode(),
                            "CountryCode" => $addressFrom->countryCodeIso2()
                        ]
                    ],
//                    "PaymentDetails" => [
//                        "ShipmentCharge" => [
//                            "Type" => "01",
//                            "BillShipper" => [
//                                "AccountNumber" => $this->credentials->accountNumber()
//                            ]
//                        ]
//                    ],
//                    "Service" => [
//                        "Code" => "04",
//                        "Description" => "UPS Canada Express Saver"
//                    ],
//                    "NumOfPieces" => "1",
                    "Package" => [
//                        "SimpleRate" => [
//                            "Description" => "SimpleRateDescription",
//                            "Code" => "XS"
//                        ],
                        "PackagingType" => [
                            "Code" => "00",
                            "Description" => "Packaging"
                        ],
                    ]
                ]
            ]
        ];


        /** @var Box $box */
        foreach ($boxes as $box) {
            $requestItem = $request;
//                $requestItem['RateRequest']['Shipment']['Service']['Code'] = (string) $serviceProviderServiceItem->serviceCode();
//                $requestItem['RateRequest']['Shipment']['Service']['Description'] = $serviceProviderServiceItem->serviceName();

            $requestItem['RateRequest']['PickupType']['Code'] = '01';
            $requestItem['RateRequest']['CustomerClassification']['Code'] = '01';
            $requestItem['RateRequest']['DeliveryTimeInformation']['PackageBillType'] = '02';
            $requestItem['RateRequest']['Shipment']['ShipmentRatingOptions']['NegotiatedRatesIndicator'] = 'Y';

            $requestItem['RateRequest']['Shipment']['Package']['Dimensions']['UnitOfMeasurement']['Code'] = $box->unitOfMeasurementSize() == Length::CM ? 'CM' : 'IN';
            $requestItem['RateRequest']['Shipment']['Package']['Dimensions']['UnitOfMeasurement']['Description'] = $box->unitOfMeasurementSize() == Length::CM ? 'Centimeters' : 'Inches';
            $requestItem['RateRequest']['Shipment']['Package']['Dimensions']['Length'] = (string) $box->length();
            $requestItem['RateRequest']['Shipment']['Package']['Dimensions']['Width'] = (string) $box->width();
            $requestItem['RateRequest']['Shipment']['Package']['Dimensions']['Height'] = (string) $box->height();

//            $requestItem['RateRequest']['Shipment']['Package']['PackageServiceOptions'] = [
//                'DeclaredValue' => [
//                    'CurrencyCode' => 'USD',
//                    'MonetaryValue' => '100',
//                ],
//            ];

            $requestItem['RateRequest']['Shipment']['Package']['PackageWeight']['UnitOfMeasurement']['Code'] = $box->unitOfMeasurementWeight() == Weight::KG ? 'KGS' : 'LBS';
            $requestItem['RateRequest']['Shipment']['Package']['PackageWeight']['UnitOfMeasurement']['Description'] = $box->unitOfMeasurementWeight() == Weight::KG ? 'Kilograms' : 'Pounds';
            $requestItem['RateRequest']['Shipment']['Package']['PackageWeight']['Weight'] = (string) $box->weight();

            $client = new \GuzzleHttp\Client();

            try {
                $response = $client->post('https://wwwcie.ups.com/api/rating/v1/Shop', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->token(),
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'x-merchant-id' => $this->credentials->userId(),
                    ],
                    'body' => json_encode($requestItem),
                ]);

                $results = new ServiceProviderRateCollection();

                $responseJson = json_decode($response->getBody()->getContents());

                if (isset($responseJson->RateResponse->RatedShipment)) {
                    foreach ($responseJson->RateResponse->RatedShipment as $priceQuote) {
                        if ($serviceProviderService !== null) {
                            if ($serviceProviderService->serviceCode() !== $priceQuote->Service->Code) {
                                continue;
                            }
                        }

                        $serviceProviderServiceItem = new ServiceProviderService($priceQuote->Service->Code, self::serviceCodes()['Domestic'][$priceQuote->Service->Code] ?? self::serviceCodes()['International'][$priceQuote->Service->Code] ?? $priceQuote->Service->Code);
                        $serviceProviderRate = new ServiceProviderRate($serviceProviderServiceItem, $priceQuote->TotalCharges->MonetaryValue, (array) $priceQuote);
                        $results->addServicePrice($serviceProviderRate);
                    }
                }

                return $results;

            } catch (RequestException $e) {
                $code = $e->getCode();

                $jsonError = json_decode($e->getResponse()->getBody(), true);

                $code = (int) $jsonError['response']['errors'][0]['code'] ?? $code;
                $message = $jsonError['response']['errors'][0]['message'] ?? $code ?? 'Invalid Shipment Parameters';

                // The requested service is unavailable between the selected locations
                if ($code === 111210 || $code === 111217) {
                    continue;
                }

                // The requested service is invalid from the selected origin
                if ($code === 111100) {
                    continue;
                }

                // Shipper's UPS Account is not enabled for the requested UPS SurePost service
                // TODO: Throw runtime error
                if ($code === 112077) {
                    continue;
                }

                throw match ($code) {
                    401, 250003 => new InvalidCredentials('Invalid ' . self::NAME . ' credentials'),
                    111617, 111056, 111057 => new InvalidShipmentParameters($message ?? 'Invalid Shipment Parameters'),
                    default => $e,
                };
            } catch (\Exception $e) {
                throw $e;
            }
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

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->post('https://wwwcie.ups.com/security/v1/oauth/token', [
                'auth' => [
                    $this->credentials->clientId(),
                    $this->credentials->clientSecret()
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'x-merchant-id' => $this->credentials->userId(),
                ],
                'body' => 'grant_type=client_credentials',
            ]);
        } catch (RequestException $e) {
            $code = $e->getCode();
            $jsonError = json_decode($e->getResponse()->getBody(), true);

            $code = (int) $jsonError['response']['errors'][0]['code'] ?? $code;
            $message = $jsonError['response']['errors'][0]['message'] ?? $code ?? 'Invalid Shipment Parameters';

            throw match ($e->getCode()) {
                401 => new InvalidCredentials('Invalid ' . self::NAME . ' credentials'),
                default => new InvalidShipmentParameters($message),
            };
        } catch (\Exception $e) {
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

        $request = [
            "ShipmentRequest" => [
                "Request" => [
                    "SubVersion" => "1801",
                    "RequestOption" => "nonvalidate",
                    "TransactionReference" => [
                        "CustomerContext" => ""
                    ]
                ],
                "Shipment" => [
                    "Description" => "Shipment to " . $shipTo->attentionName(),
                    "Shipper" => [
                        "Name" => ($shipFrom->address()->companyName() != '') ? $shipFrom->address()->companyName() : $shipFrom->address()->fullName(),
                        "AttentionName" => $shipTo->attentionName(),
                        "CompanyDisplayableName" => $shipFrom->company(),
                        "ShipperNumber" => $this->credentials->accountNumber(),
                        "Phone" => [
                            "Number" => $shipFrom->phone()->e164(),
                            "Extension" => $shipFrom->phone()->extension()
                        ],
                        "Address" => [
                            "AddressLine" => [
                                $shipFrom->address()->line1(),
                                $shipFrom->address()->line2(),
                            ],
                            "City" => $shipFrom->address()->city(),
                            "StateProvinceCode" => $shipFrom->address()->stateCodeIso2(),
                            "PostalCode" => $shipFrom->address()->postalCode(),
                            "CountryCode" => $shipFrom->address()->countryCodeIso2()
                        ]
                    ],
                    "ShipTo" => [
                        "Name" => ($shipTo->address()->companyName() != '') ? $shipTo->address()->companyName() : $shipTo->address()->fullName(),
                        "AttentionName" => $shipTo->attentionName(),
                        "CompanyDisplayableName" => $shipTo->company(),
                        "Phone" => [
                            "Number" => $shipTo->phone()->e164(),
                        ],
                        "Address" => [
                            "AddressLine" => [
                                $shipTo->address()->line1(),
                                $shipTo->address()->line2(),
                            ],
                            "City" => $shipTo->address()->city(),
                            "StateProvinceCode" => $shipTo->address()->stateCodeIso2(),
                            "PostalCode" => $shipTo->address()->postalCode(),
                            "CountryCode" => $shipTo->address()->countryCodeIso2()
                        ]
                    ],
                    "ShipFrom" => [
                        "Name" => ($shipFrom->address()->companyName() != '') ? $shipFrom->address()->companyName() : $shipFrom->address()->fullName(),
                        "Address" => [
                            "AddressLine" => [
                                $shipFrom->address()->line1(),
                                $shipFrom->address()->line2(),
                            ],
                            "City" => $shipFrom->address()->city(),
                            "StateProvinceCode" => $shipFrom->address()->stateCodeIso2(),
                            "PostalCode" => $shipFrom->address()->postalCode(),
                            "CountryCode" => $shipFrom->address()->countryCodeIso2()
                        ]
                    ],
                    "PaymentInformation" => [
                        "ShipmentCharge" => [
                            "Type" => "01",
                            "BillShipper" => [
                                "AccountNumber" => $this->credentials->accountNumber()
                            ]
                        ]
                    ],
                    "Service" => [
                        "Code" => $serviceProviderService->serviceCode(),
                        "Description" => $serviceProviderService->serviceName()
                    ],
                    "Package" => [
//                        "SimpleRate" => [
//                            "Description" => "SimpleRateDescription",
//                            "Code" => "XS"
//                        ],
                        "Packaging" => [
                            "Code" => "02",
                            "Description" => "Packaging"
                        ],
                    ]
                ],
                "LabelSpecification" => [
                    "LabelImageFormat" => [
                        "Code" => "GIF",
                        "Description" => "GIF"
                    ],
                    "HTTPUserAgent" => "Mozilla/4.5"
                ]
            ]
        ];

        $results = new ServiceProviderShipmentCollection();

        /** @var Box $box */
        foreach ($boxes as $box) {
            $requestItem = $request;
//                $requestItem['ShipmentRequest']['Shipment']['Service']['Code'] = (string) $serviceProviderServiceItem->serviceCode();
//                $requestItem['ShipmentRequest']['Shipment']['Service']['Description'] = $serviceProviderServiceItem->serviceName();

            $requestItem['ShipmentRequest']['Shipment']['Package']['Dimensions']['UnitOfMeasurement']['Code'] = $box->unitOfMeasurementSize() == Length::CM ? 'CM' : 'IN';
            $requestItem['ShipmentRequest']['Shipment']['Package']['Dimensions']['UnitOfMeasurement']['Description'] = $box->unitOfMeasurementSize() == Length::CM ? 'Centimeters' : 'Inches';
            $requestItem['ShipmentRequest']['Shipment']['Package']['Dimensions']['Length'] = (string) $box->length();
            $requestItem['ShipmentRequest']['Shipment']['Package']['Dimensions']['Width'] = (string) $box->width();
            $requestItem['ShipmentRequest']['Shipment']['Package']['Dimensions']['Height'] = (string) $box->height();

            $requestItem['ShipmentRequest']['Shipment']['Package']['PackageWeight']['UnitOfMeasurement']['Code'] = $box->unitOfMeasurementWeight() == Weight::KG ? 'KGS' : 'LBS';
            $requestItem['ShipmentRequest']['Shipment']['Package']['PackageWeight']['UnitOfMeasurement']['Description'] = $box->unitOfMeasurementWeight() == Weight::KG ? 'Kilograms' : 'Pounds';
            $requestItem['ShipmentRequest']['Shipment']['Package']['PackageWeight']['Weight'] = (string) $box->weight();

            $requestItem = array_merge_recursive($customData, $requestItem);

            $client = new \GuzzleHttp\Client();

            try {
                $response = $client->post('https://wwwcie.ups.com/api/shipments/v1/ship?additionaladdressvalidation=string', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->token(),
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'x-merchant-id' => $this->credentials->userId(),
                        'transId' => md5(microtime(true)),
                        'transactionSrc' => 'testing',
                    ],
                    'body' => json_encode($requestItem),
                ]);

                $responseJson = json_decode($response->getBody()->getContents(), true);

                $success = $responseJson['ShipmentResponse']['Response']['ResponseStatus']['Code'] == 1;
                $trackingNumber = $responseJson['ShipmentResponse']['ShipmentResults']['PackageResults']['TrackingNumber'];
                $shippingLabelFormat = $responseJson['ShipmentResponse']['ShipmentResults']['PackageResults']['ShippingLabel']['ImageFormat']['Code'];
                $shippingLabelData = $responseJson['ShipmentResponse']['ShipmentResults']['PackageResults']['ShippingLabel']['GraphicImage'];

                if ($success) {
                    $results->push(new ServiceProviderShipment($trackingNumber, $shippingLabelData, $shippingLabelFormat, $responseJson));
                }
            } catch (RequestException $e) {
                $code = $e->getCode();

                $jsonError = json_decode($e->getResponse()->getBody(), true);

                $code = (int) $jsonError['response']['errors'][0]['code'] ?? $code;
                $message = $jsonError['response']['errors'][0]['message'] ?? $code ?? 'Invalid Shipment Parameters';

                // The requested service is unavailable between the selected locations
                if ($code === 111210 || $code === 111217) {
                    continue;
                }

                // The requested service is invalid from the selected origin
                if ($code === 111100) {
                    continue;
                }

                // Shipper's UPS Account is not enabled for the requested UPS SurePost service
                // TODO: Throw runtime error
                if ($code === 112077) {
                    continue;
                }

                throw match ($code) {
                    401, 250003 => new InvalidCredentials('Invalid ' . self::NAME . ' credentials'),
                    111617, 111056, 111057, 120201, 120512, 121100 => new InvalidShipmentParameters($message ?? 'Invalid Shipment Parameters'),
                    default => $e,
                };
            } catch (\Exception $e) {
                throw $e;
            }
        }

        if ($results->isNotEmpty()) {
            return $results;
        }

        throw new ShipmentNotCreated('Unable to create shipment.');
    }


}
