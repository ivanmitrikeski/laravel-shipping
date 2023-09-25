<?php

namespace Mitrik\Tests\Feature;

use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxMetric;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidAddress;
use Mitrik\Shipping\ServiceProviders\ServiceUSPS\ServiceUSPS;
use Mitrik\Shipping\ServiceProviders\ServiceUSPS\ServiceUSPSCredentials;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxOverweight;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidCredentials;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidShipmentParameters;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class USPSRateTest extends TestCase
{
    /**
     * Test domestic rate for a single box
     *
     * @return void
     * @throws InvalidAddress
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     */
    public function test_usps_domestic_shipping_rate_response()
    {
        $credentials = new ServiceUSPSCredentials(env('USPS_USERNAME'), env('USPS_PASSWORD'), env('SHIPPING_SANDBOX'));
        $usps = new ServiceUSPS($credentials);

        $rates = $usps->rate(
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
     * @throws InvalidAddress
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     */
    public function test_usps_domestic_shipping_international_rate_response()
    {
        $credentials = new ServiceUSPSCredentials(env('USPS_USERNAME'), env('USPS_PASSWORD'), env('SHIPPING_SANDBOX'));
        $usps = new ServiceUSPS($credentials);

        $rates = $usps->rate(
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
    public function test_usps_domestic_shipping_rates_response()
    {
        $credentials = new ServiceUSPSCredentials(env('USPS_USERNAME'), env('USPS_PASSWORD'), env('SHIPPING_SANDBOX'));
        $usps = new ServiceUSPS($credentials);

        $rates = $usps->rates(
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
     * @throws InvalidAddress
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     */
    public function test_usps_overweight_response()
    {
        $this->expectException(BoxOverweight::class);

        $credentials = new ServiceUSPSCredentials(env('USPS_USERNAME'), env('USPS_PASSWORD'), env('SHIPPING_SANDBOX'));
        $usps = new ServiceUSPS($credentials);

        $rates = $usps->rate(
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
     * @throws InvalidAddress
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     */
    public function test_usps_invalid_credentials_response()
    {
        $this->expectException(InvalidCredentials::class);

        $credentials = new ServiceUSPSCredentials('1', '2');
        $usps = new ServiceUSPS($credentials);

        $rates = $usps->rate(
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
     * @throws InvalidAddress
     * @throws InvalidCredentials
     * @throws InvalidShipmentParameters
     */
    public function test_usps_invalid_box_response()
    {
        $this->expectException(InvalidShipmentParameters::class);

        $credentials = new ServiceUSPSCredentials(env('USPS_USERNAME'), env('USPS_PASSWORD'), env('SHIPPING_SANDBOX'));
        $usps = new ServiceUSPS($credentials);

        $rates = $usps->rate(
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

}
