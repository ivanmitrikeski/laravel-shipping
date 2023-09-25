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
        Schema::create('shipping_option_prices', function (Blueprint $table) {
            $table->id();
            $table->uuid()->index();
            $table->foreignIdFor(ShippingService::class)->constrained();
            $table->foreignIdFor(ShippingOption::class)->constrained();
            $table->foreignIdFor(ShippingBox::class)->constrained();
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
        Schema::dropIfExists('shipping_option_prices');
    }
};
