<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFleetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')->unique();
            $table->string('display_name');
            $table->string('load_capacity')->nullable();
            $table->enum('is_enabled', ['yes', 'no'])->default('yes');
            $table->timestamps();
        });
        Schema::create('vehicles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vehicle_type_id');
            $table->string('plate_no')->unique();
            $table->string('vin')->unique();
            $table->string('brand');
            $table->string('model');
            $table->integer('initial_millage');
            $table->dateTime('purchase_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('engine_type', ['Diesel', 'Kerosene', 'Petrol'])->default('Diesel');
            $table->timestamps();
            $table->softDeletes();
            // $table->foreign('vehicle_type_id')->references('id')->on('vehicle_types')
            //     ->onUpdate('cascade')->onDelete('cascade');

        });
        Schema::create('drivers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('employee_no');
            $table->string('license_no');
            $table->dateTime('license_issue_date')->nullable();
            $table->dateTime('license_expiry_date')->nullable();
            $table->text('emergency_contact_details')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // $table->foreign('user_id')->references('id')->on('users')
            //     ->onUpdate('cascade')->onDelete('cascade');
        });
        Schema::create('vehicle_drivers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vehicle_id');
            $table->integer('driver_id');
            $table->enum('type', ['Primary', 'Assistant'])->default('Primary');
            $table->timestamps();
            // $table->foreign('vehicle_id')->references('id')->on('vehicles')
            //     ->onUpdate('cascade')->onDelete('cascade');
            // $table->foreign('driver_id')->references('id')->on('drivers')
            //     ->onUpdate('cascade')->onDelete('cascade');
        });
        Schema::create('vehicle_expenses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('warehouse_id');
            $table->integer('vehicle_id');
            $table->integer('automobile_engineer_id');
            $table->string('service_type');
            $table->text('details')->nullable();
            $table->double('amount', 15, 2);
            $table->enum('status', ['Pending', 'Declined', 'Approved', 'Treated'])->default('Pending');
            $table->timestamp('service_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // $table->foreign('vehicle_id')->references('id')->on('vehicles')
            //     ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle_types');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('vehicle_driver');
        Schema::dropIfExists('vehicle_expenses');
    }
}
