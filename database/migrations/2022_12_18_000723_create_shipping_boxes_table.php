<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mitrik\Shipping\Models\ShippingBox;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_boxes', function (Blueprint $table) {
            $table->id();
            $table->uuid()->index();
            $table->double('length');
            $table->double('width');
            $table->double('height');
            $table->double('max_weight');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['length', 'width', 'height']);
        });

        $modelShippingBox = new ShippingBox();
        $modelShippingBox->length = 35;
        $modelShippingBox->width = 26;
        $modelShippingBox->height = 5;
        $modelShippingBox->max_weight = 5;
        $modelShippingBox->save();

        $modelShippingBox = new ShippingBox();
        $modelShippingBox->length = 39;
        $modelShippingBox->width = 26;
        $modelShippingBox->height = 12;
        $modelShippingBox->max_weight = 5;
        $modelShippingBox->save();

        $modelShippingBox = new ShippingBox();
        $modelShippingBox->length = 40;
        $modelShippingBox->width = 30;
        $modelShippingBox->height = 19;
        $modelShippingBox->max_weight = 5;
        $modelShippingBox->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_boxes');
    }
};
