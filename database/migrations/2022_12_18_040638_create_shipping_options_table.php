<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mitrik\Shipping\Models\ShippingOption;
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
        Schema::create('shipping_options', function (Blueprint $table) {
            $table->id();
            $table->uuid()->index();
            $table->foreignIdFor(ShippingService::class)->constrained();
            $table->string('code');
            $table->string('name');
            $table->boolean('is_enabled')->default(true)->index();
            $table->boolean('is_internal')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        $collectionShippingService = ShippingService::all();

        /** @var ShippingService $modelShippingService */
        foreach ($collectionShippingService as $modelShippingService) {
            if (class_exists($modelShippingService->service_provider_class)) {
                $serviceCodes = $modelShippingService->service_provider_class::serviceCodes();

                foreach ($serviceCodes as $serviceCode => $serviceName) {
                    if (is_array($serviceName)) {
                        $serviceCodesSub = $serviceName;
                        foreach ($serviceCodesSub as $serviceCodeSub => $serviceNameSub) {
                            $modelShippingOption = new ShippingOption();
                            $modelShippingOption->shipping_service_id = $modelShippingService->id;
                            $modelShippingOption->code = $serviceCodeSub;
                            $modelShippingOption->name = $serviceNameSub;
                            $modelShippingOption->is_internal = true;
                            $modelShippingOption->save();
                        }
                    } else {
                        $modelShippingOption = new ShippingOption();
                        $modelShippingOption->shipping_service_id = $modelShippingService->id;
                        $modelShippingOption->code = $serviceCode;
                        $modelShippingOption->name = $serviceName;
                        $modelShippingOption->is_internal = true;
                        $modelShippingOption->save();
                    }
                }
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
        Schema::dropIfExists('shipping_options');
    }
};
