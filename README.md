# Shipping package for Laravel
Supported shipping providers:
- UPS REST/OAuth API
- FedEx REST/OAuth API
- Canada Post
- USPS
- Purolator

Package can be used outside the Laravel environment as well (by making individual shipping provider requests).

Supported API services:

| Shipping Provider | Rate API | Shipment API |
|:------------------|:--------:|:------------:|
| UPS REST          |    ✓     |      ✓       |
| FedEx REST        |    ✓     |      ✓       |
| Canada Post       |    ✓     |      ✓       |
| Purolator         |    ✓     |      ✓       |
| USPS              |    ✓     |      -       |

### Install package
```bash
composer require ivanmitrikeski/laravel-shipping
```

### Run migrations

```bash
php artisan migrate
```

### Setup .env variables
```
CANADA_POST_CUSTOMER_NUMBER=
CANADA_POST_USERNAME=
CANADA_POST_PASSWORD=

PUROLATOR_KEY=
PUROLATOR_PASSWORD=
PUROLATOR_BILLING_ACCOUNT=
PUROLATOR_REGISTERED_ACCOUNT=
PUROLATOR_USER_TOKEN=

UPS_CLIENT_ID=
UPS_CLIENT_SECRET=
UPS_USER_ID=
UPS_ACCOUNT_NUMBER=

USPS_USERNAME=
USPS_PASSWORD=

FEDEX_CLIENT_ID=
FEDEX_CLIENT_SECRET=
FEDEX_ACCOUNT_NUMBER=

SHIPPING_SANDBOX=false
```

### Using sandbox credentials

In order to use test credentials just add param "true" in the *Credentials() object or set environment variable SHIPPING_SANDBOX=false.

### Models

There are 4 Eloquent models available: ShippingService, ShippingBox, ShippingOption and ShippingOptionPrice.

ShippingBox and ShippingOptionPrice are used for service Flat. Creating ShippingBox and creating ShippingOptionPrice will allow users to set flat rate shipping prices.

### Shipping Quotes

#### Retrieve all shipping rates, for all enabled shipping providers.

```php
$exceptions = [];

$rates = app(\Mitrik\Shipping\Facades\Shipping::class)->rates(
    new Address(
        'Ivan',
        'Mitrikeski',
        '',
        '321 Lakeshore Rd W',
        '',
        'Mississauga',
        'L5H 1G0',
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
    new \Mitrik\Shipping\ServiceProviders\Box\BoxCollection([
        new BoxMetric(20, 10, 5, 1)
    ]),
    $exceptions
);
```

Fourth parameter `$exceptions` is optional. If provided function will push all exception to provided array. If not provided, an exception will be thrown each time there is an issue retrieving a quote. 

##### Canada Post

```php
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxMetric;
use Mitrik\Shipping\ServiceProviders\ServiceCanadaPost\ServiceCanadaPost;
use Mitrik\Shipping\ServiceProviders\ServiceCanadaPost\ServiceCanadaPostCredentials;

$credentials = new ServiceCanadaPostCredentials(env('CANADA_POST_CUSTOMER_NUMBER'), env('CANADA_POST_USERNAME'), env('CANADA_POST_PASSWORD'));
$canadaPost = new ServiceCanadaPost($credentials);

$rates = $canadaPost->rate(
    new Address(
        'Ivan',
        'Mitrikeski',
        '',
        '321 Lakeshore Rd W',
        '',
        'Mississauga',
        'L5H 1G0',
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
        new BoxMetric(20, 10, 5, 1)
    ])
);
```

#### Purolator

```php
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxMetric;
use Mitrik\Shipping\ServiceProviders\ServicePurolator\ServicePurolator;
use Mitrik\Shipping\ServiceProviders\ServicePurolator\ServicePurolatorCredentials;

$credentials = new ServicePurolatorCredentials(env('PUROLATOR_KEY'), env('PUROLATOR_PASSWORD'), env('PUROLATOR_BILLING_ACCOUNT'), env('PUROLATOR_REGISTERED_ACCOUNT'), env('PUROLATOR_USER_TOKEN'), env('PUROLATOR_SANDBOX'));
$purolator = new ServicePurolator($credentials);

$rates = $purolator->rate(
    new Address(
        'Ivan',
        'Mitrikeski',
        '',
        '321 Lakeshore Rd W',
        '',
        'Mississauga',
        'L5H 1G0',
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
        new BoxMetric(20, 10, 5, 1)
    ])
);

```

#### UPS

```php
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxMetric;
use Mitrik\Shipping\ServiceProviders\ServiceUPS\ServiceUPS;
use Mitrik\Shipping\ServiceProviders\ServiceUPS\ServiceUPSCredentials;

$credentials = new ServiceUPSCredentials(env('UPS_ACCESS_KEY'), env('UPS_USER_ID'), env('UPS_PASSWORD'));
$serviceUPS = new ServiceUPS($credentials);

$rates = $serviceUPS->rate(
    new Address(
        'Ivan',
        'Mitrikeski',
        '',
        '321 Lakeshore Rd W',
        '',
        'Mississauga',
        'L5H 1G0',
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
        new BoxMetric(20, 10, 5, 1)
    ])
);
```


#### USPS

```php
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxMetric;
use Mitrik\Shipping\ServiceProviders\ServiceUSPS\ServiceUSPS;
use Mitrik\Shipping\ServiceProviders\ServiceUSPS\ServiceUSPSCredentials;

$credentials = new ServiceUSPSCredentials(env('USPS_USERNAME'), env('USPS_PASSWORD'));
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
```

#### FedEx

```php
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxMetric;
use Mitrik\Shipping\ServiceProviders\ServiceFedEx\ServiceFedEx;
use Mitrik\Shipping\ServiceProviders\ServiceFedEx\ServiceFedExCredentials;

$credentials = new ServiceFedExCredentials(env('FEDEX_CLIENT_ID'), env('FEDEX_CLIENT_SECRET'), env('FEDEX_ACCOUNT_NUMBER'));
$fedEx = new ServiceFedEx($credentials);

$rates = $fedEx->rate(
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
```

#### Flat

```php
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxMetric;
use Mitrik\Shipping\ServiceProviders\ServiceFlat\ServiceFlat;



$flat = new ServiceFlat();

$rates = $flat->rate(
    new Address(
        'Ivan',
        'Mitrikeski',
        '',
        '321 Lakeshore Rd W',
        '',
        'Mississauga',
        'L5H 1G0',
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
```

#### Creating shipping boxes

```php
$modelShippingBox = new ShippingBox();
$modelShippingBox->length = 35;
$modelShippingBox->width = 26;
$modelShippingBox->height = 5;
$modelShippingBox->max_weight = 5;
$modelShippingBox->save();
```

#### Creating shipping option for Flat service

```php
$modelShippingService = ShippingService::whereName('Flat')->first();

$modelShippingOption = new ShippingOption();
$modelShippingOption->shipping_service_id = $modelShippingService->id;
$modelShippingOption->code = 'FREE.PICKUP';
$modelShippingOption->name = 'Free Pickup';
$modelShippingOption->is_enabled = true;
$modelShippingOption->is_internal = false;
$modelShippingOption->save();
```

#### Creating Flat service prices

```php
$modelShippingBox = ShippingBox::whereUuid('SOME_BOX_UUID')->first();
$modelShippingOption = ShippingOption::whereUuid('SOME_OPTION_UUID')->first();


$modelShippingOptionPrice = new ShippingOptionPrice();
$modelShippingOptionPrice->shipping_box_id = $modelShippingBox->id;
$modelShippingOptionPrice->shipping_option_id = $modelShippingOption->id;
$modelShippingOptionPrice->shipping_service_id = $modelShippingOption->shipping_service_id;
$modelShippingOptionPrice->price = 10.99;
$modelShippingOptionPrice->save();
```


### Creating Shipments

##### Canada Post

```php
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxMetric;
use Mitrik\Shipping\ServiceProviders\ServiceCanadaPost\ServiceCanadaPost;
use Mitrik\Shipping\ServiceProviders\ServiceCanadaPost\ServiceCanadaPostCredentials;

$credentials = new ServiceCanadaPostCredentials(env('CANADA_POST_CUSTOMER_NUMBER'), env('CANADA_POST_USERNAME'), env('CANADA_POST_PASSWORD'));
$canadaPost = new ServiceCanadaPost($credentials);

$result = $canadaPost->ship(
    new ShipFrom(
        name: 'Ivan Mitrikeski',
        attentionName: 'Ivan Mitrikeski',
        address: new Address(
            'Ivan',
            'Mitrikeski',
            '',
            '321 Lakeshore Rd W',
            '',
            'Mississauga',
            'L5H 1G0',
            'ON',
            'CA'
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
```

#### Purolator

```php
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxMetric;
use Mitrik\Shipping\ServiceProviders\ServicePurolator\ServicePurolator;
use Mitrik\Shipping\ServiceProviders\ServicePurolator\ServicePurolatorCredentials;

$credentials = new ServicePurolatorCredentials(env('PUROLATOR_KEY'), env('PUROLATOR_PASSWORD'), env('PUROLATOR_BILLING_ACCOUNT'), env('PUROLATOR_REGISTERED_ACCOUNT'), env('PUROLATOR_USER_TOKEN'), env('PUROLATOR_SANDBOX'));
$purolator = new ServicePurolator($credentials);

$result = $purolator->ship(
    new ShipFrom(
        'Ivan Mitrikeski',
        'Ivan Mitrikeski',
        new Address(
            'Ivan',
            'Mitrikeski',
            '',
            '321 Lakeshore Rd W',
            '',
            'Mississauga',
            'L5H 1G0',
            'ON',
            'CA'
        ),
        new Phone('+1', '555', '1231234'),
        ''
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
    new ServiceProviderService('PurolatorExpress', '')
);
```

#### UPS

```php
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxMetric;
use Mitrik\Shipping\ServiceProviders\ServiceUPS\ServiceUPS;
use Mitrik\Shipping\ServiceProviders\ServiceUPS\ServiceUPSCredentials;

$credentials = new ServiceUPSCredentials(env('UPS_ACCESS_KEY'), env('UPS_USER_ID'), env('UPS_PASSWORD'));
$serviceUPS = new ServiceUPS($credentials);

$result = $serviceUPS->ship(
    new ShipFrom(
        'Ivan Mitrikeski',
        'Ivan Mitrikeski',
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
        new Phone('+1', '555', '1231234'),
        ''
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
        new BoxImperial(20, 10, 5, 1)
    ]),
    new ServiceProviderService('03', '')
);
```


#### FedEx

```php
use Mitrik\Shipping\ServiceProviders\Address\Address;
use Mitrik\Shipping\ServiceProviders\Box\BoxCollection;
use Mitrik\Shipping\ServiceProviders\Box\BoxMetric;
use Mitrik\Shipping\ServiceProviders\ServiceFedEx\ServiceFedEx;
use Mitrik\Shipping\ServiceProviders\ServiceFedEx\ServiceFedExCredentials;

$credentials = new ServiceFedExCredentials(env('FEDEX_CLIENT_ID'), env('FEDEX_CLIENT_SECRET'), env('FEDEX_ACCOUNT_NUMBER'));
$fedEx = new ServiceFedEx($credentials);

$result = $fedEx->ship(
    new ShipFrom(
        'Ivan Mitrikeski',
        'Ivan Mitrikeski',
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
        new Phone('+1', '555', '1231234'),
        ''
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
        new BoxImperial(20, 10, 5, 1)
    ]),
    new ServiceProviderService('FEDEX_2_DAY', '')
);
```


# Testing

Update phpunit.xml env variables before running tests. 

```xml
<php>
    <env name="DB_CONNECTION" value="mysql"/>
    <env name="DB_HOST" value="localhost"/>
    <env name="DB_PORT" value="3306"/>
    <env name="DB_DATABASE" value=""/>
    <env name="DB_USERNAME" value="root"/>
    <env name="DB_PASSWORD" value=""/>
    
    <env name="TEST_SHIPPING_ORIGIN_FIRST_NAME" value=""/>
    <env name="TEST_SHIPPING_ORIGIN_LAST_NAME" value=""/>
    <env name="TEST_SHIPPING_ORIGIN_COMPANY" value=""/>
    <env name="TEST_SHIPPING_ORIGIN_LINE1" value=""/>
    <env name="TEST_SHIPPING_ORIGIN_LINE2" value=""/>
    <env name="TEST_SHIPPING_ORIGIN_CITY" value=""/>
    <env name="TEST_SHIPPING_ORIGIN_POSTAL_CODE" value=""/>
    <env name="TEST_SHIPPING_ORIGIN_STATE" value=""/>
    <env name="TEST_SHIPPING_ORIGIN_COUNTRY" value=""/>
    
    <env name="CANADA_POST_CUSTOMER_NUMBER" value=""/>
    <env name="CANADA_POST_USERNAME" value=""/>
    <env name="CANADA_POST_PASSWORD" value=""/>
    
    <env name="UPS_CLIENT_ID" value=""/>
    <env name="UPS_CLIENT_SECRET" value=""/>
    <env name="UPS_USER_ID" value=""/>
    <env name="UPS_ACCOUNT_NUMBER" value=""/>
    
    <env name="PUROLATOR_KEY" value=""/>
    <env name="PUROLATOR_PASSWORD" value=""/>
    <env name="PUROLATOR_BILLING_ACCOUNT" value=""/>
    <env name="PUROLATOR_REGISTERED_ACCOUNT" value=""/>
    <env name="PUROLATOR_USER_TOKEN" value=""/>
    
    <env name="FEDEX_CLIENT_ID" value=""/>
    <env name="FEDEX_CLIENT_SECRET" value=""/>
    <env name="FEDEX_ACCOUNT_NUMBER" value=""/>
    
    <env name="USPS_USERNAME" value=""/>
    <env name="USPS_PASSWORD" value=""/>
    
    <env name="SHIPPING_SANDBOX" value="true"/>
</php>
```

```bash
composer test
```
