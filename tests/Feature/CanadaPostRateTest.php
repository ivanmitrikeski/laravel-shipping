<?php

namespace Mitrik\Tests\Feature;

use GuzzleHttp\Exception\GuzzleException;
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxMetric;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxEmpty;
use Mitrik\Shipping\ServiceProviders\Exceptions\PriceNotFound;
use Mitrik\Shipping\ServiceProviders\ServiceCanadaPost\ServiceCanadaPost;
use Mitrik\Shipping\ServiceProviders\ServiceCanadaPost\ServiceCanadaPostCredentials;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxOverweight;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidCredentials;
use Mitrik\Shipping\ServiceProviders\Exceptions\InvalidShipmentParameters;
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

}
