<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mitrik\Shipping\Models\ShippingBox;
use Mitrik\Shipping\Models\ShippingBoxPrice;
use Mitrik\Shipping\Models\ShippingOption;
use Mitrik\Shipping\Models\ShippingOptionPrice;
use Mitrik\Shipping\Models\ShippingService;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('shipping.table_prefix', 'laravel_shipping_') . 'shipping_option_prices', function (Blueprint $table) {
            $table->id();
            $table->uuid()->index();
            $table->foreignIdFor(ShippingService::class)->constrained(table: config('shipping.table_prefix', 'laravel_shipping_') . 'shipping_services', indexName: 'ls_sop_ss_id');
            $table->foreignIdFor(ShippingOption::class)->constrained(table: config('shipping.table_prefix', 'laravel_shipping_') . 'shipping_options', indexName: 'ls_sop_so_id');
            $table->foreignIdFor(ShippingBox::class)->constrained(table: config('shipping.table_prefix', 'laravel_shipping_') . 'shipping_boxes', indexName: 'ls_sop_sb_id');
            $table->double('price', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        /** @var ShippingService $modelShippingService */
        $modelShippingService = ShippingService::whereName('Flat')->first();

        $collectionShippingBoxes = ShippingBox::all();

        foreach ($collectionShippingBoxes as $key => $modelShippingBox) {
            foreach ($modelShippingService->shippingOptions as $modelShippingOption) {
                $modelShippingOptionPrice = new ShippingOptionPrice();
                $modelShippingOptionPrice->shipping_service_id = $modelShippingService->id;
                $modelShippingOptionPrice->shipping_option_id = $modelShippingOption->id;
                $modelShippingOptionPrice->shipping_box_id = $modelShippingBox->id;
                $modelShippingOptionPrice->price = str_contains($modelShippingOption->code, 'FREE') ? 0 : 10 * (1 + $key);
                $modelShippingOptionPrice->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('shipping.table_prefix', 'laravel_shipping_')  . 'shipping_option_prices');
    }
};
