<?php

namespace Mitrik\Tests\Feature;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Mitrik\Shipping\Facades\Shipping;
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxMetric;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class FacadeTest extends TestCase
{
    public function createDatabaseConnection(): void
    {
        $capsule = new Manager();

        $capsule->addConnection([
            'driver'    => env('DB_CONNECTION'),
            'host'      => env('DB_HOST'),
            'database'  => env('DB_DATABASE'),
            'username'  => env('DB_USERNAME'),
            'password'  => env('DB_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'port'      => env('DB_PORT')
        ]);

        $resolver = new \Illuminate\Database\ConnectionResolver();
        $resolver->addConnection('default', $capsule->getConnection('default'));
        $resolver->setDefaultConnection('default');

        Model::setConnectionResolver($resolver);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function test_shipping_usa()
    {
        $this->createDatabaseConnection();

        $exceptions = [];

        $facade = new Shipping();

        $rates = $facade->rates(
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
            ]),
            $exceptions
        );

        $this->assertArrayHasKey(0, $rates);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function test_shipping_international()
    {
        $exceptions = [];

        $facade = new Shipping();

        $rates = $facade->rates(
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
            ]),
            $exceptions
        );

        $this->assertArrayHasKey(0, $rates);
    }
}
