<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleExpenseDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_expense_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('warehouse_id');
            $table->integer('vehicle_expense_id');
            $table->string('vehicle_part');
            $table->enum('service_type', ['Repair', 'Replacement'])->default('Repair');
            $table->double('amount', 15, 2);
            $table->timestamps();
        });

        Schema::create('automobile_engineers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('phone_no');
            $table->string('email');
            $table->string('company_name');
            $table->string('workshop_address');
            $table->timestamps();
            // $table->foreign('user_id')->references('id')->on('users')
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
        Schema::dropIfExists('vehicle_expense_details');
        Schema::dropIfExists('automobile_engineers');
    }
}
