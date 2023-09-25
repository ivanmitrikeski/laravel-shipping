<?php

namespace Mitrik\Tests\Feature;

use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxMetric;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxEmpty;
use Mitrik\Shipping\ServiceProviders\Exceptions\BoxOverweight;
use Mitrik\Shipping\ServiceProviders\Exceptions\PriceNotFound;
use Mitrik\Shipping\ServiceProviders\ServiceFlat\ServiceFlat;
use Mitrik\Shipping\ServiceProviders\ServiceProviderRate\ServiceProviderRate;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class FlatRateTest extends TestCase
{
    /**
     * Test domestic rate for a single box
     *
     * @return void
     * @throws BoxEmpty
     * @throws BoxOverweight
     * @throws PriceNotFound
     */
    public function test_flat_rate_response()
    {
        $flat = new ServiceFlat();

        $rates = $flat->rate(
            new Address(
                'Ivan',
                'Mitrikeski',
                '',
                '100 City Centre Dr',
                '',
                'Mississauga',
                'L5R 3R4',
                'ON',
                'CA'
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
                new BoxMetric(35, 26, 5, 1)
            ])
        );

        $this->assertArrayHasKey(0, $rates);

        /** @var ServiceProviderRate $rate */
        foreach ($rates as $rate) {
            if ($rate->serviceProviderService()->serviceCode() === 'INTERNAL.FREE.PICKUP') {
                $this->assertEquals(0, $rate->price());
            } else {
                $this->assertEquals(10.00, $rate->price());
            }
        }
    }

    /**
     * Test domestic rate for a single box
     *
     * @return void
     * @throws BoxEmpty
     * @throws BoxOverweight
     * @throws PriceNotFound
     */
    public function test_flat_rate_overweight_response()
    {
        $this->expectException(BoxOverweight::class);

        $flat = new ServiceFlat();

        $rates = $flat->rate(
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
                new BoxMetric(35, 26, 5, 10)
            ])
        );
    }

}
