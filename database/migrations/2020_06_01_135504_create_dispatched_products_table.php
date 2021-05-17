<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDispatchedProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatched_products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('warehouse_id');
            $table->integer('waybill_id');
            $table->integer('item_stock_id');
            $table->integer('quantity_supplied');
            $table->string('status');
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
        Schema::dropIfExists('dispatched_products');
    }
}
