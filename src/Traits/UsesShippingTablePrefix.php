<?php

namespace Mitrik\Shipping\Traits;

trait UsesShippingTablePrefix
{
    public function getTable(): string
    {
        $prefix = app()->bound('config')
            ? config('shipping.table_prefix', 'laravel_shipping_')
            : 'laravel_shipping_';

        return $prefix . parent::getTable();
    }
}
