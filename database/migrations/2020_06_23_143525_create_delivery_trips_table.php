<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_trips', function (Blueprint $table) {
            $table->id();
            $table->string('trip_no');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('delivery_trip_waybill', function (Blueprint $table) {
            $table->unsignedInteger('delivery_trip_id');
            $table->unsignedInteger('waybill_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_trips');
        Schema::dropIfExists('delivery_trip_waybill');
    }
}
