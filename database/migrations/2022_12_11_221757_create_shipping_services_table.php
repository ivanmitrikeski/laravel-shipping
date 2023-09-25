<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
        Schema::create('shipping_services', function (Blueprint $table) {
            $table->id();
            $table->uuid()->index();
            $table->string('name')->index();
            $table->string('service_provider_class');
            $table->boolean('is_enabled')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        $modelShippingService = new ShippingService();
        $modelShippingService->name = 'CanadaPost';
        $modelShippingService->service_provider_class = 'Mitrik\\Shipping\\ServiceProviders\\ServiceCanadaPost\\ServiceCanadaPost';
        $modelShippingService->save();

        $modelShippingService->uuid = '6f7bd8c8-4ebd-4b2d-b9f3-64c04d15f741';
        $modelShippingService->save();


        $modelShippingService = new ShippingService();
        $modelShippingService->name = 'Purolator';
        $modelShippingService->service_provider_class = 'Mitrik\\Shipping\\ServiceProviders\\ServicePurolator\\ServicePurolator';
        $modelShippingService->save();

        $modelShippingService->uuid = '6f7bd8c8-4ebd-4b2d-b9f3-64c04d15f742';
        $modelShippingService->save();


        $modelShippingService = new ShippingService();
        $modelShippingService->name = 'UPS';
        $modelShippingService->service_provider_class = 'Mitrik\\Shipping\\ServiceProviders\\ServiceUPS\\ServiceUPS';
        $modelShippingService->save();

        $modelShippingService->uuid = '6f7bd8c8-4ebd-4b2d-b9f3-64c04d15f743';
        $modelShippingService->save();

        $modelShippingService = new ShippingService();
        $modelShippingService->name = 'Flat';
        $modelShippingService->service_provider_class = 'Mitrik\\Shipping\\ServiceProviders\\ServiceFlat\\ServiceFlat';
        $modelShippingService->save();

        $modelShippingService->uuid = '6f7bd8c8-4ebd-4b2d-b9f3-64c04d15f744';
        $modelShippingService->save();

        $modelShippingService = new ShippingService();
        $modelShippingService->name = 'USPS';
        $modelShippingService->service_provider_class = 'Mitrik\\Shipping\\ServiceProviders\\ServiceUSPS\\ServiceUSPS';
        $modelShippingService->save();

        $modelShippingService->uuid = '6f7bd8c8-4ebd-4b2d-b9f3-64c04d15f745';
        $modelShippingService->save();

        $modelShippingService = new ShippingService();
        $modelShippingService->name = 'FedEx';
        $modelShippingService->service_provider_class = 'Mitrik\\Shipping\\ServiceProviders\\ServiceFedEx\\ServiceFedEx';
        $modelShippingService->save();

        $modelShippingService->uuid = '6f7bd8c8-4ebd-4b2d-b9f3-64c04d15f746';
        $modelShippingService->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_services');
    }
};
