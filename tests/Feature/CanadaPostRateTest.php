<?php

namespace Mitrik\Tests\Feature;

use GuzzleHttp\Exception\GuzzleException;
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxMetric;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxEmpty;
use Mitrik\Shipping\ServiceProviders\Exceptions\PriceNotFound;
use Mitrik\Shipping\ServiceProviders\Phone\Phone;
use Mitrik\Shipping\ServiceProviders\ServiceCanadaPost\ServiceCanadaPost;
use Mitrik\Shipping\ServiceProviders\ServiceCanadaPost\ServiceCanadaPostCredentials;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxOverweight;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidCredentials;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidShipmentParameters;
use Mitrik\Shipping\ServiceProviders\ServiceProviderService\ServiceProviderService;
use Mitrik\Shipping\ServiceProviders\ServiceProviderShipment\ServiceProviderShipmentCustomsValue;
use Mitrik\Shipping\ServiceProviders\ShipFrom\ShipFrom;
use Mitrik\Shipping\ServiceProviders\ShipTo\ShipTo;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class CanadaPostRateTest extends TestCase
{
    // These are public Canada Post test keys
    /**
     * @var string
     */
    private string $customerNumber = '2004381';
    /**
     * @var string
     */
    private string $username = '6e93d53968881714';
    /**
     * @var string
     */
    private string $password = '0bfa9fcb9853d1f51ee57a';

    /**
     * Test domestic rate for a single box
     *
     * @return void
     * @throws BoxEmpty
     * @throws BoxOverweight
     * @throws GuzzleException
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     * @throws PriceNotFound
     */
    public function test_canadapost_domestic_shipping_rate_response()
    {
        $credentials = new ServiceCanadaPostCredentials($this->customerNumber, $this->username, $this->password, env('SHIPPING_SANDBOX'));
        $canadaPost = new ServiceCanadaPost($credentials);

        $rates = $canadaPost->rate(
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
     * @throws BoxEmpty
     * @throws BoxOverweight
     * @throws GuzzleException
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     * @throws PriceNotFound
     */
    public function test_canadapost_domestic_shipping_us_rate_response()
    {
        $credentials = new ServiceCanadaPostCredentials($this->customerNumber, $this->username, $this->password, env('SHIPPING_SANDBOX'));
        $canadaPost = new ServiceCanadaPost($credentials);

        $rates = $canadaPost->rate(
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
     * @throws BoxEmpty
     * @throws BoxOverweight
     * @throws GuzzleException
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     * @throws PriceNotFound
     */
    public function test_canadapost_domestic_shipping_international_rate_response()
    {
        $credentials = new ServiceCanadaPostCredentials($this->customerNumber, $this->username, $this->password, env('SHIPPING_SANDBOX'));
        $canadaPost = new ServiceCanadaPost($credentials);

        $rates = $canadaPost->rate(
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
     * @throws BoxEmpty
     * @throws BoxOverweight
     * @throws GuzzleException
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     * @throws PriceNotFound
     */
    public function test_canadapost_domestic_shipping_rates_response()
    {
        $credentials = new ServiceCanadaPostCredentials($this->customerNumber, $this->username, $this->password, env('SHIPPING_SANDBOX'));
        $canadaPost = new ServiceCanadaPost($credentials);

        $rates = $canadaPost->rates(
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
     * @throws BoxEmpty
     * @throws BoxOverweight
     * @throws GuzzleException
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     * @throws PriceNotFound
     */
    public function test_canadapost_overweight_response()
    {
        $this->expectException(BoxOverweight::class);

        $credentials = new ServiceCanadaPostCredentials($this->customerNumber, $this->username, $this->password, env('SHIPPING_SANDBOX'));
        $canadaPost = new ServiceCanadaPost($credentials);

        $rates = $canadaPost->rate(
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
     * @throws BoxEmpty
     * @throws BoxOverweight
     * @throws GuzzleException
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     * @throws PriceNotFound
     */
    public function test_canadapost_invalid_credentials_response()
    {
        $this->expectException(InvalidCredentials::class);

        $credentials = new ServiceCanadaPostCredentials('1', '2', '3');
        $canadaPost = new ServiceCanadaPost($credentials);

        $rates = $canadaPost->rate(
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
     * @throws BoxOverweight
     * @throws GuzzleException
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     * @throws BoxEmpty
     * @throws PriceNotFound
     */
    public function test_canadapost_invalid_box_response()
    {
        $this->expectException(InvalidShipmentParameters::class);

        $credentials = new ServiceCanadaPostCredentials($this->customerNumber, $this->username, $this->password, env('SHIPPING_SANDBOX'));
        $canadaPost = new ServiceCanadaPost($credentials);

        $rates = $canadaPost->rate(
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
     * @throws BoxEmpty
     * @throws BoxOverweight
     * @throws GuzzleException
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     * @throws PriceNotFound
     */
    public function test_canadapost_domestic_shipping_shipment_response()
    {
        $credentials = new ServiceCanadaPostCredentials($this->customerNumber, $this->username, $this->password, env('SHIPPING_SANDBOX'));
        $canadaPost = new ServiceCanadaPost($credentials);

        $result = $canadaPost->ship(
            new ShipFrom(
                name: env('TEST_SHIPPING_ORIGIN_FIRST_NAME') . ' ' . env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                attentionName: env('TEST_SHIPPING_ORIGIN_FIRST_NAME') . ' ' . env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                address: new Address(
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
                phone: new Phone('+1', '555', '1231234'),
                company: 'Test'
            ),
            new ShipTo(
                'John Smith',
                'John Smith',
                new Address(
                    'John',
                    'Smith',
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
            new ServiceProviderService('DOM.RP', ''),
            null,
            [
                'delivery-spec' => [
                    'settlement-info' => [
                        'contract-id' => '0042708517'
                    ]
                ]
            ]
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
     * @throws BoxEmpty
     * @throws BoxOverweight
     * @throws GuzzleException
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     * @throws PriceNotFound
     */
    public function test_canadapost_domestic_shipping_us_shipment_response()
    {
        $credentials = new ServiceCanadaPostCredentials($this->customerNumber, $this->username, $this->password, env('SHIPPING_SANDBOX'));
        $canadaPost = new ServiceCanadaPost($credentials);

        $result = $canadaPost->ship(
            new ShipFrom(
                name: env('TEST_SHIPPING_ORIGIN_FIRST_NAME') . ' ' . env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                attentionName: env('TEST_SHIPPING_ORIGIN_FIRST_NAME') . ' ' . env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                address: new Address(
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
                phone: new Phone('+1', '555', '1231234'),
                company: 'Test'
            ),
            new ShipTo(
                'John Smith',
                'John Smith',
                new Address(
                    'John',
                    'Smith',
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
            new ServiceProviderService('USA.EP', ''),
            new ServiceProviderShipmentCustomsValue(300, 'USD'),
            [
                'delivery-spec' => [
                    'settlement-info' => [
                        'contract-id' => '0042708517'
                    ],
                    'customs' => [
                        'currency' => 'CAD',
                        'reason-for-export' => 'SOG',
                        'sku-list' => [
                            'item' => [
                                'customs-number-of-units' => 1,
                                'customs-description' => 'Customs Description',
                                'sku' => 'SKU12345',
                                'unit-weight' => 1,
                                'customs-value-per-unit' => 1,
                            ]
                        ]
                    ],
                    'options' => [
                        'option' => [
                            'option-code' => 'RASE'
                        ]
                    ]
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
     * @throws BoxEmpty
     * @throws BoxOverweight
     * @throws GuzzleException
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     * @throws PriceNotFound
     */
    public function test_canadapost_domestic_shipping_international_shipment_response()
    {
        $credentials = new ServiceCanadaPostCredentials($this->customerNumber, $this->username, $this->password, env('SHIPPING_SANDBOX'));
        $canadaPost = new ServiceCanadaPost($credentials);

        $result = $canadaPost->ship(
            new ShipFrom(
                name: env('TEST_SHIPPING_ORIGIN_FIRST_NAME') . ' ' . env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                attentionName: env('TEST_SHIPPING_ORIGIN_FIRST_NAME') . ' ' . env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                address: new Address(
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
                phone: new Phone('+1', '555', '1231234'),
                company: 'Test'
            ),
            new ShipTo(
                'John Smith',
                'John Smith',
                new Address(
                    'John',
                    'Smith',
                    '',
                    'Panoramastrasse 1A',
                    '',
                    'Berlin',
                    '10178',
                    'BRG',
                    'DE'
                ),
                new Phone('+1', '555', '1231234')
            ),
            new BoxCollection([
                new BoxMetric(20, 10, 5, 1)
            ]),
            new ServiceProviderService('INT.IP.SURF', ''),
            new ServiceProviderShipmentCustomsValue(300, 'USD'),
            [
                'delivery-spec' => [
                    'settlement-info' => [
                        'contract-id' => '0042708517'
                    ],
                    'customs' => [
                        'currency' => 'CAD',
                        'reason-for-export' => 'SOG',
                        'sku-list' => [
                            'item' => [
                                'customs-number-of-units' => 1,
                                'customs-description' => 'Customs Description',
                                'sku' => 'SKU12345',
                                'unit-weight' => 1,
                                'customs-value-per-unit' => 1,
                            ]
                        ]
                    ],
                    'options' => [
                        'option' => [
                            'option-code' => 'RASE'
                        ]
                    ]
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
     * @throws BoxEmpty
     * @throws BoxOverweight
     * @throws GuzzleException
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     * @throws PriceNotFound
     */
    public function test_canadapost_domestic_shipping_shipments_response()
    {
        $credentials = new ServiceCanadaPostCredentials($this->customerNumber, $this->username, $this->password, env('SHIPPING_SANDBOX'));
        $canadaPost = new ServiceCanadaPost($credentials);

        $result = $canadaPost->ship(
            new ShipFrom(
                name: env('TEST_SHIPPING_ORIGIN_FIRST_NAME') . ' ' . env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                attentionName: env('TEST_SHIPPING_ORIGIN_FIRST_NAME') . ' ' . env('TEST_SHIPPING_ORIGIN_LAST_NAME'),
                address: new Address(
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
                phone: new Phone('+1', '555', '1231234'),
                company: 'Test'
            ),
            new ShipTo(
                'John Smith',
                'John Smith',
                new Address(
                    'John',
                    'Smith',
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
            new ServiceProviderService('DOM.PC', ''),
            null,
            [
                'delivery-spec' => [
                    'settlement-info' => [
                        'contract-id' => '0042708517'
                    ],
                ]
            ]
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
