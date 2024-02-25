<?php

namespace Mitrik\Tests\Feature;

use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxMetric;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidOriginPostalCode;
use Mitrik\Shipping\ServiceProviders\Phone\Phone;
use Mitrik\Shipping\ServiceProviders\ServiceProviderService\ServiceProviderService;
use Mitrik\Shipping\ServiceProviders\ServicePurolator\ServicePurolator;
use Mitrik\Shipping\ServiceProviders\ServicePurolator\ServicePurolatorCredentials;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxOverweight;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidCredentials;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidShipmentParameters;
use Mitrik\Shipping\ServiceProviders\ShipFrom\ShipFrom;
use Mitrik\Shipping\ServiceProviders\ShipTo\ShipTo;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class PurolatorTest extends TestCase
{
    /**
     * Test domestic rate for a single box
     *
     * @return void
     * @throws InvalidCredentials
     * @throws InvalidOriginPostalCode
     * @throws InvalidShipmentParameters|\Mitrik\Shipping\ServiceProviders\Exceptions\PriceNotFound
     */
    public function test_purolator_domestic_shipping_rate_response()
    {
        $credentials = new ServicePurolatorCredentials(env('PUROLATOR_KEY'), env('PUROLATOR_PASSWORD'), env('PUROLATOR_BILLING_ACCOUNT'), env('PUROLATOR_REGISTERED_ACCOUNT'), env('PUROLATOR_USER_TOKEN'), env('SHIPPING_SANDBOX'));
        $purolator = new ServicePurolator($credentials);

        $rates = $purolator->rate(
            new Address(
                env('TEST_SHIPPING_ORIGIN_FIRST_NAME'),
                env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                env('TEST_SHIPPING_ORIGIN_COMPANY'),
                env('TEST_SHIPPING_ORIGIN_LINE1'),
                env('TEST_SHIPPING_ORIGIN_LINE2'),
                env('TEST_SHIPPING_ORIGIN_CITY'),
                env('TEST_SHIPPING_ORIGIN_POSTAL_CODE'),
                env('TEST_SHIPPING_ORIGIN_STATE'),
                env('TEST_SHIPPING_ORIGIN_COUNTRY')
            ),
            new Address(
                'Ivan',
                'Mitrikeski',
                '',
                '100 City Centre Dr',
                '',
                'Mississauga',
                'L5B 2C9',
                'ON',
                'CA'
            ),
            new BoxCollection([
                new BoxMetric(20, 10, 5, 1)
            ])
        );

        $this->assertArrayHasKey(0, $rates);
    }

    /**
     * Test US rate for a single box
     *
     * @return void
     * @throws InvalidCredentials
     * @throws InvalidOriginPostalCode
     * @throws InvalidShipmentParameters|\Mitrik\Shipping\ServiceProviders\Exceptions\PriceNotFound
     */
    public function test_purolator_domestic_shipping_us_rate_response()
    {
        $credentials = new ServicePurolatorCredentials(env('PUROLATOR_KEY'), env('PUROLATOR_PASSWORD'), env('PUROLATOR_BILLING_ACCOUNT'), env('PUROLATOR_REGISTERED_ACCOUNT'), env('PUROLATOR_USER_TOKEN'), env('SHIPPING_SANDBOX'));
        $purolator = new ServicePurolator($credentials);

        $rates = $purolator->rate(
            new Address(
                env('TEST_SHIPPING_ORIGIN_FIRST_NAME'),
                env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                env('TEST_SHIPPING_ORIGIN_COMPANY'),
                env('TEST_SHIPPING_ORIGIN_LINE1'),
                env('TEST_SHIPPING_ORIGIN_LINE2'),
                env('TEST_SHIPPING_ORIGIN_CITY'),
                env('TEST_SHIPPING_ORIGIN_POSTAL_CODE'),
                env('TEST_SHIPPING_ORIGIN_STATE'),
                env('TEST_SHIPPING_ORIGIN_COUNTRY')
            ),
            new Address(
                'Ivan',
                'Mitrikeski',
                '',
                '1 Wall St',
                '',
                'New York',
                '10005',
                'NY',
                'US'
            ),
            new BoxCollection([
                new BoxMetric(20, 10, 5, 1)
            ])
        );

        $this->assertArrayHasKey(0, $rates);
    }

    /**
     * Test International rate for a single box
     *
     * @return void
     * @throws InvalidCredentials
     * @throws InvalidOriginPostalCode
     * @throws InvalidShipmentParameters|\Mitrik\Shipping\ServiceProviders\Exceptions\PriceNotFound
     */
    public function test_purolator_domestic_shipping_international_rate_response()
    {
        $credentials = new ServicePurolatorCredentials(env('PUROLATOR_KEY'), env('PUROLATOR_PASSWORD'), env('PUROLATOR_BILLING_ACCOUNT'), env('PUROLATOR_REGISTERED_ACCOUNT'), env('PUROLATOR_USER_TOKEN'), env('SHIPPING_SANDBOX'));
        $purolator = new ServicePurolator($credentials);

        $rates = $purolator->rate(
            new Address(
                env('TEST_SHIPPING_ORIGIN_FIRST_NAME'),
                env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                env('TEST_SHIPPING_ORIGIN_COMPANY'),
                env('TEST_SHIPPING_ORIGIN_LINE1'),
                env('TEST_SHIPPING_ORIGIN_LINE2'),
                env('TEST_SHIPPING_ORIGIN_CITY'),
                env('TEST_SHIPPING_ORIGIN_POSTAL_CODE'),
                env('TEST_SHIPPING_ORIGIN_STATE'),
                env('TEST_SHIPPING_ORIGIN_COUNTRY')
            ),
            new Address(
                'Ivan',
                'Mitrikeski',
                '',
                'Panoramastrasse 1A',
                '',
                'Berlin',
                '10178',
                '',
                'DE'
            ),
            new BoxCollection([
                new BoxMetric(20, 10, 5, 1)
            ])
        );

        $this->assertArrayHasKey(0, $rates);
    }

    /**
     * Test domestic rate for multiple boxes
     *
     * @return void
     */
    public function test_purolator_domestic_shipping_rates_response()
    {
        $credentials = new ServicePurolatorCredentials(env('PUROLATOR_KEY'), env('PUROLATOR_PASSWORD'), env('PUROLATOR_BILLING_ACCOUNT'), env('PUROLATOR_REGISTERED_ACCOUNT'), env('PUROLATOR_USER_TOKEN'), env('SHIPPING_SANDBOX'));
        $purolator = new ServicePurolator($credentials);

        $rates = $purolator->rates(
            new Address(
                env('TEST_SHIPPING_ORIGIN_FIRST_NAME'),
                env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                env('TEST_SHIPPING_ORIGIN_COMPANY'),
                env('TEST_SHIPPING_ORIGIN_LINE1'),
                env('TEST_SHIPPING_ORIGIN_LINE2'),
                env('TEST_SHIPPING_ORIGIN_CITY'),
                env('TEST_SHIPPING_ORIGIN_POSTAL_CODE'),
                env('TEST_SHIPPING_ORIGIN_STATE'),
                env('TEST_SHIPPING_ORIGIN_COUNTRY')
            ),
            new Address(
                'Ivan',
                'Mitrikeski',
                '',
                '100 City Centre Dr',
                '',
                'Mississauga',
                'L5B 2C9',
                'ON',
                'CA'
            ),
            new BoxCollection([
                new BoxMetric(20, 10, 5, 1),
                new BoxMetric(20, 10, 5, 1),
            ])
        );

        $this->assertArrayHasKey(0, $rates);
    }

    /**
     * Test domestic rate for a single box
     *
     * @return void
     * @throws InvalidCredentials
     * @throws InvalidOriginPostalCode
     * @throws InvalidShipmentParameters|\Mitrik\Shipping\ServiceProviders\Exceptions\PriceNotFound
     */
    public function test_purolator_overweight_response()
    {
        $this->expectException(BoxOverweight::class);

        $credentials = new ServicePurolatorCredentials(env('PUROLATOR_KEY'), env('PUROLATOR_PASSWORD'), env('PUROLATOR_BILLING_ACCOUNT'), env('PUROLATOR_REGISTERED_ACCOUNT'), env('PUROLATOR_USER_TOKEN'), env('SHIPPING_SANDBOX'));
        $purolator = new ServicePurolator($credentials);

        $rates = $purolator->rate(
            new Address(
                env('TEST_SHIPPING_ORIGIN_FIRST_NAME'),
                env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                env('TEST_SHIPPING_ORIGIN_COMPANY'),
                env('TEST_SHIPPING_ORIGIN_LINE1'),
                env('TEST_SHIPPING_ORIGIN_LINE2'),
                env('TEST_SHIPPING_ORIGIN_CITY'),
                env('TEST_SHIPPING_ORIGIN_POSTAL_CODE'),
                env('TEST_SHIPPING_ORIGIN_STATE'),
                env('TEST_SHIPPING_ORIGIN_COUNTRY')
            ),
            new Address(
                'Ivan',
                'Mitrikeski',
                '',
                '100 City Centre Dr',
                '',
                'Mississauga',
                'L5B 2C9',
                'ON',
                'CA'
            ),
            new BoxCollection([
                new BoxMetric(20, 10, 5, 10, 5)
            ])
        );
    }

    /**
     * Test domestic rate for a single box
     *
     * @return void
     * @throws InvalidCredentials
     * @throws InvalidOriginPostalCode
     * @throws InvalidShipmentParameters|\Mitrik\Shipping\ServiceProviders\Exceptions\PriceNotFound
     */
    public function test_purolator_invalid_credentials_response()
    {
        $this->expectException(InvalidCredentials::class);

        $credentials = new ServicePurolatorCredentials('1', '2', '3', '4', '5', true);
        $purolator = new ServicePurolator($credentials);

        $rates = $purolator->rate(
            new Address(
                env('TEST_SHIPPING_ORIGIN_FIRST_NAME'),
                env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                env('TEST_SHIPPING_ORIGIN_COMPANY'),
                env('TEST_SHIPPING_ORIGIN_LINE1'),
                env('TEST_SHIPPING_ORIGIN_LINE2'),
                env('TEST_SHIPPING_ORIGIN_CITY'),
                env('TEST_SHIPPING_ORIGIN_POSTAL_CODE'),
                env('TEST_SHIPPING_ORIGIN_STATE'),
                env('TEST_SHIPPING_ORIGIN_COUNTRY')
            ),
            new Address(
                'Ivan',
                'Mitrikeski',
                '',
                '100 City Centre Dr',
                '',
                'Mississauga',
                'L5B 2C9',
                'ON',
                'CA'
            ),
            new BoxCollection([
                new BoxMetric(20, 10, 5, 1)
            ])
        );
    }

    /**
     * Test domestic rate for a single box
     *
     * @return void
     * @throws InvalidCredentials
     * @throws InvalidOriginPostalCode
     * @throws InvalidShipmentParameters|\Mitrik\Shipping\ServiceProviders\Exceptions\PriceNotFound
     */
    public function test_purolator_invalid_box_response()
    {
        $this->expectException(InvalidShipmentParameters::class);

        $credentials = new ServicePurolatorCredentials(env('PUROLATOR_KEY'), env('PUROLATOR_PASSWORD'), env('PUROLATOR_BILLING_ACCOUNT'), env('PUROLATOR_REGISTERED_ACCOUNT'), env('PUROLATOR_USER_TOKEN'), env('SHIPPING_SANDBOX'));
        $purolator = new ServicePurolator($credentials);

        $rates = $purolator->rate(
            new Address(
                env('TEST_SHIPPING_ORIGIN_FIRST_NAME'),
                env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                env('TEST_SHIPPING_ORIGIN_COMPANY'),
                env('TEST_SHIPPING_ORIGIN_LINE1'),
                env('TEST_SHIPPING_ORIGIN_LINE2'),
                env('TEST_SHIPPING_ORIGIN_CITY'),
                env('TEST_SHIPPING_ORIGIN_POSTAL_CODE'),
                env('TEST_SHIPPING_ORIGIN_STATE'),
                env('TEST_SHIPPING_ORIGIN_COUNTRY')
            ),
            new Address(
                'Ivan',
                'Mitrikeski',
                '',
                '100 City Centre Dr',
                '',
                'Mississauga',
                'L5B 2C9',
                'ON',
                'CA'
            ),
            new BoxCollection([
                new BoxMetric(200, 100, 500, 1)
            ])
        );
    }

    /**
     * Test domestic shipment for a single box
     *
     * @return void
     * @throws InvalidCredentials
     * @throws InvalidOriginPostalCode
     * @throws InvalidShipmentParameters|\Mitrik\Shipping\ServiceProviders\Exceptions\PriceNotFound
     */
    public function test_purolator_domestic_shipping_shipment_response()
    {
        $credentials = new ServicePurolatorCredentials(env('PUROLATOR_KEY'), env('PUROLATOR_PASSWORD'), env('PUROLATOR_BILLING_ACCOUNT'), env('PUROLATOR_REGISTERED_ACCOUNT'), env('PUROLATOR_USER_TOKEN'), env('SHIPPING_SANDBOX'));
        $purolator = new ServicePurolator($credentials);

        $result = $purolator->ship(
            new ShipFrom(
                env('TEST_SHIPPING_ORIGIN_FIRST_NAME') . ' ' . env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                env('TEST_SHIPPING_ORIGIN_FIRST_NAME') . ' ' . env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                new Address(
                    env('TEST_SHIPPING_ORIGIN_FIRST_NAME'),
                    env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                    env('TEST_SHIPPING_ORIGIN_COMPANY'),
                    env('TEST_SHIPPING_ORIGIN_LINE1'),
                    env('TEST_SHIPPING_ORIGIN_LINE2'),
                    env('TEST_SHIPPING_ORIGIN_CITY'),
                    env('TEST_SHIPPING_ORIGIN_POSTAL_CODE'),
                    env('TEST_SHIPPING_ORIGIN_STATE'),
                    env('TEST_SHIPPING_ORIGIN_COUNTRY')
                ),
                new Phone('+1', '555', '1231234'),
                ''
            ),
            new ShipTo(
                'John Smith',
                'John Smith',
                new Address(
                    'Ivan',
                    'Mitrikeski',
                    '',
                    '100 City Centre Dr',
                    '',
                    'Mississauga',
                    'L5B 2C9',
                    'ON',
                    'CA'
                ),
                new Phone('+1', '555', '1231234')
            ),
            new BoxCollection([
                new BoxMetric(20, 10, 5, 1)
            ]),
            new ServiceProviderService('PurolatorExpress', '')
        );

        $this->assertArrayHasKey(0, $result);
        $this->assertNotEmpty($result[0]->trackingNumber());
        $this->assertNotEmpty($result[0]->shipmentLabelData());
        $this->assertNotEmpty($result[0]->shipmentLabelDataFormat());
    }

    /**
     * Test US shipment for a single box
     *
     * @return void
     * @throws InvalidCredentials
     * @throws InvalidOriginPostalCode
     * @throws InvalidShipmentParameters|\Mitrik\Shipping\ServiceProviders\Exceptions\PriceNotFound
     */
    public function test_purolator_domestic_shipping_us_shipment_response()
    {
        $credentials = new ServicePurolatorCredentials(env('PUROLATOR_KEY'), env('PUROLATOR_PASSWORD'), env('PUROLATOR_BILLING_ACCOUNT'), env('PUROLATOR_REGISTERED_ACCOUNT'), env('PUROLATOR_USER_TOKEN'), env('SHIPPING_SANDBOX'));
        $purolator = new ServicePurolator($credentials);

        $result = $purolator->ship(
            new ShipFrom(
                env('TEST_SHIPPING_ORIGIN_FIRST_NAME') . ' ' . env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                env('TEST_SHIPPING_ORIGIN_FIRST_NAME') . ' ' . env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                new Address(
                    env('TEST_SHIPPING_ORIGIN_FIRST_NAME'),
                    env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                    env('TEST_SHIPPING_ORIGIN_COMPANY'),
                    env('TEST_SHIPPING_ORIGIN_LINE1'),
                    env('TEST_SHIPPING_ORIGIN_LINE2'),
                    env('TEST_SHIPPING_ORIGIN_CITY'),
                    env('TEST_SHIPPING_ORIGIN_POSTAL_CODE'),
                    env('TEST_SHIPPING_ORIGIN_STATE'),
                    env('TEST_SHIPPING_ORIGIN_COUNTRY')
                ),
                new Phone('+1', '555', '1231234'),
                ''
            ),
            new ShipTo(
                'John Smith',
                'John Smith',
                new Address(
                    'Ivan',
                    'Mitrikeski',
                    '',
                    '1 Wall St',
                    '',
                    'New York',
                    '10005',
                    'NY',
                    'US'
                ),
                new Phone('+1', '555', '1231234')
            ),
            new BoxCollection([
                new BoxMetric(20, 10, 5, 1)
            ]),
            new ServiceProviderService('PurolatorExpressU.S.', ''),
            null,
            [
                'Shipment' => [
                    'InternationalInformation' => [
                        'DocumentsOnlyIndicator' => true
                    ],
                ]
            ]
        );

        $this->assertArrayHasKey(0, $result);
        $this->assertNotEmpty($result[0]->trackingNumber());
        $this->assertNotEmpty($result[0]->shipmentLabelData());
        $this->assertNotEmpty($result[0]->shipmentLabelDataFormat());
    }

    /**
     * Test International shipment for a single box
     *
     * @return void
     * @throws InvalidCredentials
     * @throws InvalidOriginPostalCode
     * @throws InvalidShipmentParameters|\Mitrik\Shipping\ServiceProviders\Exceptions\PriceNotFound
     */
    public function test_purolator_domestic_shipping_international_shipment_response()
    {
        $credentials = new ServicePurolatorCredentials(env('PUROLATOR_KEY'), env('PUROLATOR_PASSWORD'), env('PUROLATOR_BILLING_ACCOUNT'), env('PUROLATOR_REGISTERED_ACCOUNT'), env('PUROLATOR_USER_TOKEN'), env('SHIPPING_SANDBOX'));
        $purolator = new ServicePurolator($credentials);

        $result = $purolator->ship(
            new ShipFrom(
                env('TEST_SHIPPING_ORIGIN_FIRST_NAME') . ' ' . env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                env('TEST_SHIPPING_ORIGIN_FIRST_NAME') . ' ' . env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                new Address(
                    env('TEST_SHIPPING_ORIGIN_FIRST_NAME'),
                    env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                    env('TEST_SHIPPING_ORIGIN_COMPANY'),
                    env('TEST_SHIPPING_ORIGIN_LINE1'),
                    env('TEST_SHIPPING_ORIGIN_LINE2'),
                    env('TEST_SHIPPING_ORIGIN_CITY'),
                    env('TEST_SHIPPING_ORIGIN_POSTAL_CODE'),
                    env('TEST_SHIPPING_ORIGIN_STATE'),
                    env('TEST_SHIPPING_ORIGIN_COUNTRY')
                ),
                new Phone('+1', '555', '1231234'),
                ''
            ),
            new ShipTo(
                'John Smith',
                'John Smith',
                new Address(
                    'Ivan',
                    'Mitrikeski',
                    '',
                    'Panoramastrasse 1A',
                    '',
                    'Berlin',
                    '10178',
                    '',
                    'DE'
                ),
                new Phone('+1', '555', '1231234')
            ),
            new BoxCollection([
                new BoxMetric(20, 10, 5, 1)
            ]),
            new ServiceProviderService('PurolatorExpressInternational', ''),
            null,
            [
                'Shipment' => [
                    'InternationalInformation' => [
                        'DocumentsOnlyIndicator' => true
                    ],
                ]
            ]
        );

        $this->assertArrayHasKey(0, $result);
        $this->assertNotEmpty($result[0]->trackingNumber());
        $this->assertNotEmpty($result[0]->shipmentLabelData());
        $this->assertNotEmpty($result[0]->shipmentLabelDataFormat());
    }

    /**
     * Test domestic shipment for multiple boxes
     *
     * @return void
     */
    public function test_purolator_domestic_shipping_shipments_response()
    {
        $credentials = new ServicePurolatorCredentials(env('PUROLATOR_KEY'), env('PUROLATOR_PASSWORD'), env('PUROLATOR_BILLING_ACCOUNT'), env('PUROLATOR_REGISTERED_ACCOUNT'), env('PUROLATOR_USER_TOKEN'), env('SHIPPING_SANDBOX'));
        $purolator = new ServicePurolator($credentials);

        $result = $purolator->ship(
            new ShipFrom(
                env('TEST_SHIPPING_ORIGIN_FIRST_NAME') . ' ' . env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                env('TEST_SHIPPING_ORIGIN_FIRST_NAME') . ' ' . env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                new Address(
                    env('TEST_SHIPPING_ORIGIN_FIRST_NAME'),
                    env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                    env('TEST_SHIPPING_ORIGIN_COMPANY'),
                    env('TEST_SHIPPING_ORIGIN_LINE1'),
                    env('TEST_SHIPPING_ORIGIN_LINE2'),
                    env('TEST_SHIPPING_ORIGIN_CITY'),
                    env('TEST_SHIPPING_ORIGIN_POSTAL_CODE'),
                    env('TEST_SHIPPING_ORIGIN_STATE'),
                    env('TEST_SHIPPING_ORIGIN_COUNTRY')
                ),
                new Phone('+1', '555', '1231234'),
                ''
            ),
            new ShipTo(
                'John Smith',
                'John Smith',
                new Address(
                    'Ivan',
                    'Mitrikeski',
                    '',
                    '100 City Centre Dr',
                    '',
                    'Mississauga',
                    'L5B 2C9',
                    'ON',
                    'CA'
                ),
                new Phone('+1', '555', '1231234')
            ),
            new BoxCollection([
                new BoxMetric(20, 10, 5, 1),
                new BoxMetric(20, 10, 5, 1),
            ]),
            new ServiceProviderService('PurolatorExpress9AM', '')
        );

        $this->assertArrayHasKey(0, $result);
        $this->assertNotEmpty($result[0]->trackingNumber());
        $this->assertNotEmpty($result[0]->shipmentLabelData());
        $this->assertNotEmpty($result[0]->shipmentLabelDataFormat());
        $this->assertArrayHasKey(1, $result);
        $this->assertNotEmpty($result[1]->trackingNumber());
        $this->assertNotEmpty($result[1]->shipmentLabelData());
        $this->assertNotEmpty($result[1]->shipmentLabelDataFormat());
    }

}
