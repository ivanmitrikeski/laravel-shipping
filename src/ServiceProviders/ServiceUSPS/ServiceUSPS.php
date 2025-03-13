<?php

namespace Mitrik\Shipping\ServiceProviders\ServiceUSPS;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use LSS\XML2Array;
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\Box;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxEmpty;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxOverweight;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidAddress;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidCredentials;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidShipmentParameters;
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
                '0' => 'First-Class Mail',
                '1' => 'Priority Mail',
                '2' => 'Priority Mail Express; Hold For Pickup',
                '3' => 'Priority Mail Express',
                '4' => 'Standard Post',
                '5' => 'Bound Printed Matter Parcels',
                '6' => 'Media Mail Parcel',
                '7' => 'Library Mail Parcel',
                '13' => 'Priority Mail Express; Flat Rate Envelope',
                '15' => 'First-Class Mail; Large Postcards',
                '16' => 'Priority Mail; Flat Rate Envelope',
                '17' => 'Priority Mail; Medium Flat Rate Box',
                '20' => 'Bound Printed Matter Flats',
                '22' => 'Priority Mail; Large Flat Rate Box',
                '23' => 'Priority Mail Express; Sunday/Holiday Delivery',
                '25' => 'Priority Mail Express; Sunday/Holiday Delivery Flat Rate Envelope',
                '27' => 'Priority Mail Express; Flat Rate Envelope Hold For Pickup',
                '28' => 'Priority Mail; Small Flat Rate Box',
                '29' => 'Priority Mail; Padded Flat Rate Envelope',
                '30' => 'Priority Mail Express; Legal Flat Rate Envelope',
                '31' => 'Priority Mail Express; Legal Flat Rate Envelope Hold For Pickup',
                '32' => 'Priority Mail Express; Sunday/Holiday Delivery Legal Flat Rate Envelope',
                '33' => 'Priority Mail; Hold For Pickup',
                '34' => 'Priority Mail; Large Flat Rate Box Hold For Pickup',
                '35' => 'Priority Mail; Medium Flat Rate Box Hold For Pickup',
                '36' => 'Priority Mail; Small Flat Rate Box Hold For Pickup',
                '37' => 'Priority Mail; Flat Rate Envelope Hold For Pickup',
                '38' => 'Priority Mail; Gift Card Flat Rate Envelope',
                '39' => 'Priority Mail; Gift Card Flat Rate Envelope Hold For Pickup',
                '40' => 'Priority Mail; Window Flat Rate Envelope',
                '41' => 'Priority Mail; Window Flat Rate Envelope Hold For Pickup',
                '42' => 'Priority Mail; Small Flat Rate Envelope',
                '43' => 'Priority Mail; Small Flat Rate Envelope Hold For Pickup',
                '44' => 'Priority Mail; Legal Flat Rate Envelope',
                '45' => 'Priority Mail; Legal Flat Rate Envelope Hold For Pickup',
                '46' => 'Priority Mail; Padded Flat Rate Envelope Hold For Pickup',
                '47' => 'Priority Mail; Regional Rate Box A',
                '48' => 'Priority Mail; Regional Rate Box A Hold For Pickup',
                '49' => 'Priority Mail; Regional Rate Box B',
                '50' => 'Priority Mail; Regional Rate Box B Hold For Pickup',
                '53' => 'First-Class; Package Service Hold For Pickup',
                '55' => 'Priority Mail Express; Flat Rate Boxes',
                '56' => 'Priority Mail Express; Flat Rate Boxes Hold For Pickup',
                '57' => 'Priority Mail Express; Sunday/Holiday Delivery Flat Rate Boxes',
                '58' => 'Priority Mail; Regional Rate Box C',
                '59' => 'Priority Mail; Regional Rate Box C Hold For Pickup',
                '61' => 'First-Class; Package Service',
                '62' => 'Priority Mail Express; Padded Flat Rate Envelope',
                '63' => 'Priority Mail Express; Padded Flat Rate Envelope Hold For Pickup',
                '64' => 'Priority Mail Express; Sunday/Holiday Delivery Padded Flat Rate Envelope',
                '77' => 'Parcel Select Ground',
                '78' => 'First-Class Mail; Metered Letter',
                '82' => 'Parcel Select Lightweight',
                '84' => 'Priority Mail Cubic',
                '88' => 'USPS Connect Local DDU',
                '89' => 'USPS Connect Local Flat Rate Bag – Small DDU',
                '90' => 'USPS Connect Local Flat Rate Bag – Large DDU',
                '91' => 'USPS Connect Local Flat Rate Box DDU',
                '92' => 'Parcel Select Ground Cubic',
                '179' => 'Parcel Select Destination Entry',
                '922' => 'Priority Mail Return Service Padded Flat Rate Envelope',
                '932' => 'Priority Mail Return Service Gift Card Flat Rate Envelope',
                '934' => 'Priority Mail Return Service Window Flat Rate Envelope',
                '936' => 'Priority Mail Return Service Small Flat Rate Envelope',
                '938' => 'Priority Mail Return Service Legal Flat Rate Envelope',
                '939' => 'Priority Mail Return Service Flat Rate Envelope',
                '946' => 'Priority Mail Return Service Regional Rate Box A',
                '947' => 'Priority Mail Return Service Regional Rate Box B',
                '962' => 'Priority Mail Return Service',
                '963' => 'Priority Mail Return Service Large Flat Rate Box',
                '964' => 'Priority Mail Return Service Medium Flat Rate Box',
                '965' => 'Priority Mail Return Service Small Flat Rate Box',
                '967' => 'Priority Mail Return Service Cubic',
                '968' => 'First-Class Package Return Service',
                '969' => 'Ground Return Service',
                '2020' => 'Bound Printed Matter Flats Hold For Pickup',
                '2071' => 'Parcel Select Ground; Hold For Pickup',
                '2077' => 'Bound Printed Matter Parcels Hold For Pickup',
                '2082' => 'Parcel Select Lightweight',
            ],
            'International' => [
                '1' => 'Priority Mail Express International',
                '2' => 'Priority Mail International',
                '4' => 'Global Express Guaranteed; (GXG)**',
                '5' => 'Global Express Guaranteed; Document',
                '6' => 'Global Express Guarantee; Non-Document Rectangular',
                '7' => 'Global Express Guaranteed; Non-Document Non-Rectangular',
                '8' => 'Priority Mail International; Flat Rate Envelope**',
                '9' => 'Priority Mail International; Medium Flat Rate Box',
                '10' => 'Priority Mail Express International; Flat Rate Envelope',
                '11' => 'Priority Mail International; Large Flat Rate Box',
                '12' => 'USPS GXG; Envelopes**',
                '13' => 'First-Class Mail; International Letter**',
                '14' => 'First-Class Mail; International Large Envelope**',
                '15' => 'First-Class Package International Service**',
                '16' => 'Priority Mail International; Small Flat Rate Box**',
                '17' => 'Priority Mail Express International; Legal Flat Rate Envelope',
                '18' => 'Priority Mail International; Gift Card Flat Rate Envelope**',
                '19' => 'Priority Mail International; Window Flat Rate Envelope**',
                '20' => 'Priority Mail International; Small Flat Rate Envelope**',
                '28' => 'Airmail M-Bag'
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

        $countryName = self::iso2ToCountryName($addressTo->countryCodeIso2());

        $requestName = 'RateV4Request';
        $url = $this->credentials->test() ? 'https://stg-secure.shippingapis.com/shippingapi.dll' : 'https://secure.shippingapis.com/shippingapi.dll';

        if ($addressFrom->countryCodeIso2() !== 'US' || $addressTo->countryCodeIso2() !== 'US') {
            $url .= '?API=IntlRateV2&XML=';
            $requestName = 'IntlRateV2Request';
        } else {
            $url .= '?API=RateV4&XML=';
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

            if ($addressFrom->countryCodeIso2() !== 'US' || $addressTo->countryCodeIso2() !== 'US') {
                $packages .= '
    <Package ID="' . $boxId . '">
        <Pounds>' . $lbs . '</Pounds>
        <Ounces>0</Ounces>
	    <Machinable>TRUE</Machinable>
	    <MailType>PACKAGE</MailType>
	    <ValueOfContents>20</ValueOfContents>
        <Country>' . $countryName . '</Country>
        <Container></Container>
        <Size></Size>
        <Width>' . $width . '</Width>
        <Length>' . $length . '</Length>
        <Height>' . $height . '</Height>
        <Girth></Girth>
        <OriginZip>' . $addressFrom->postalCode() . '</OriginZip>
        <CommercialFlag>N</CommercialFlag>
        <AcceptanceDateTime>' . str_replace('+', '-', date('c', strtotime('tomorrow 9am'))) . '</AcceptanceDateTime>
        <DestinationPostalCode>' . $addressTo->postalCode() . '</DestinationPostalCode>
    </Package>
            ';
            } else {
                $packages .= '
    <Package ID="' . $boxId . '">
        <Service>ALL</Service>
        <ZipOrigination>' . $addressFrom->postalCode() . '</ZipOrigination>
        <ZipDestination>' . $addressTo->postalCode() . '</ZipDestination>
        <Pounds>' . $lbs . '</Pounds>
        <Ounces>0</Ounces>
        <Container></Container>
        <Width>' . $width . '</Width>
        <Length>' . $length . '</Length>
        <Height>' . $height . '</Height>
        <Girth></Girth>
        <Machinable>false</Machinable>
    </Package>
            ';
            }


        }
        $xml = '
<' . $requestName . ' USERID="' . $this->credentials->username() . '">
    <Revision>2</Revision>
    ' . $packages . '
</' . $requestName . '>';


        $client = new \GuzzleHttp\Client();
        $response = $client->get($url . $xml);
        $data = $response->getBody()->getContents();

        $array = XML2Array::createArray($data);

        if (isset($array['Error'])) {
            $code = (string) $array['Error']['Number'];
            $error = $array['Error']['Description'];

            throw match ($code) {
                '80040B1A' => new InvalidCredentials('Invalid ' . self::NAME . ' credentials'),
                default => new Exception($error),
            };
        }

        $error = $array['IntlRateV2Response']['Package']['Error'] ?? $array['RateV4Response']['Package']['Error'] ?? null;
        $error = ($error !== null) ? $error : ($array['IntlRateV2Response']['Error'] ?? $array['RateV4Response']['Error'] ?? null);
        if ($error !== null) {
            $code = $error['Number'];
            $errorMessage = $error['Description'];

            throw match ($code) {
                '-2147218043', '-2147219385' => new InvalidShipmentParameters($errorMessage ?? 'Invalid Shipment Parameters'),
                '-2147219497', '-2147219056' => new InvalidAddress($errorMessage ?? 'Invalid Address'),
                default => new Exception($errorMessage),
            };
        }

	if(isset($array['IntlRateV2Response'])) {
            $package = $array['IntlRateV2Response']['Package'] ?? $array['RateV4Response']['Package'] ?? [];
            $services = $array['IntlRateV2Response']['Package']['Service'] ?? $array['RateV4Response']['Package']['Postage'] ?? [];

            $country = isset($array['IntlRateV2Response']) ? 'US' : 'International';
	}
	elseif(isset($array['RateV4Response'])) {
            $package = $array['RateV4Response']['Package'] ?? $array['RateV4Response']['Package'] ?? [];
            $services = $array['RateV4Response']['Package']['Service'] ?? $array['RateV4Response']['Package']['Postage'] ?? [];

            $country = 'US';
        }    

        $packages = [];
        if ($boxes->count() > 1) {
            foreach ($package as $packageBox) {
                $services = $packageBox['Service'] ?? $packageBox['Postage'] ?? [];
                $packages[] = [
                    'services' => $services
                ];
            }
        } else {
            $services = $package['Service'] ?? $package['Postage'] ?? [];
            $packages[] = [
                'services' => $services
            ];
        }


        foreach ($packages as $package) {
            foreach ($package['services'] as $service) {

                if (!isset($service['@attributes']['ID']) && !isset($service['@attributes']['CLASSID'])) {
                    continue;
                }

                $serviceCode = $service['@attributes']['ID'] ?? $service['@attributes']['CLASSID'];
                $serviceName = $this->serviceCodes()[$country][$serviceCode] ?? null;
                if ($serviceName === null) {
                    continue;
                }

                $serviceProviderService = new ServiceProviderService($serviceCode, $serviceName);
                $serviceProviderRate = new ServiceProviderRate($serviceProviderService, $service['Postage'] ?? $service['Rate'], (array) $service);

                $results->addServicePrice($serviceProviderRate);
            }
        }

        return $results;
    }

    /**
     * @param $countryCode
     * @return string
     * @throws InvalidAddress
     */
    private function iso2ToCountryName($countryCode): string
    {
        $counties = [
            'AF' => 'Afghanistan',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana',
            'BR' => 'Brazil',
            'BN' => 'Brunei',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'CV' => 'Cape Verde',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CG' => 'Congo',
            'CD' => 'Congo (Democratic Republic)',
            'CR' => 'Costa Rica',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'TL' => 'East Timor',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GR' => 'Greece',
            'GD' => 'Grenada',
            'GT' => 'Guatemala',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HN' => 'Honduras',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'CI' => 'Ivory Coast',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Laos',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MK' => 'North Macedonia',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'MX' => 'Mexico',
            'FM' => 'Micronesia',
            'MD' => 'Moldova',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'KP' => 'North Korea',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'QA' => 'Qatar',
            'RO' => 'Romania',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'KN' => 'Saint Kitts and Nevis',
            'LC' => 'Saint Lucia',
            'VC' => 'Saint Vincent and the Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'ST' => 'Sao Tome and Principe',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'KR' => 'South Korea',
            'SS' => 'South Sudan',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SZ' => 'Eswatini',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syria',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania',
            'TH' => 'Thailand',
            'TG' => 'Togo',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TV' => 'Tuvalu',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States of America',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VA' => 'Vatican City',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        ];

        return $counties[$countryCode] ?? throw new InvalidAddress('Invalid country code.');
    }

    public function ship(ShipFrom $shipFrom, ShipTo $shipTo, BoxCollection $boxes, ServiceProviderService $serviceProviderService, ServiceProviderShipmentCustomsValue|null $serviceProviderShipmentCustomsValue = null, $customData = []): ServiceProviderShipmentCollection
    {
        throw new \Exception('Not implemented yet.');
    }
}
