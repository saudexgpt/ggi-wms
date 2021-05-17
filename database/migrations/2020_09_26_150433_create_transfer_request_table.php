<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('supply_warehouse_id');
            $table->integer('request_warehouse_id');
            $table->string('request_by');
            $table->string('request_number');
            $table->string('status');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['request_number']);
        });
        Schema::create('transfer_request_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('supply_warehouse_id');
            $table->integer('request_warehouse_id');
            $table->integer('transfer_request_id');
            $table->integer('item_id');
            $table->integer('quantity');
            $table->string('type');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('transfer_request_item_batches', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('supply_warehouse_id');
            $table->integer('request_warehouse_id');
            $table->integer('transfer_request_id');
            $table->integer('transfer_request_item_id');
            $table->integer('item_stock_sub_batch_id');
            $table->integer('to_supply');
            $table->integer('quantity');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('transfer_request_dispatched_products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('supply_warehouse_id');
            $table->integer('request_warehouse_id');
            $table->integer('transfer_request_waybill_id');
            $table->integer('transfer_request_waybill_item_id');
            $table->integer('item_stock_sub_batch_id');
            $table->integer('quantity_supplied');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('transfer_request_waybills', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('supply_warehouse_id');
            $table->integer('transfer_request_waybill_no');
            $table->integer('status');
            $table->integer('confirmed_by');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('transfer_request_waybill_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('supply_warehouse_id');
            $table->integer('transfer_request_waybill_id');
            $table->integer('item_id');
            $table->integer('transfer_request_id');
            $table->integer('transfer_request_item_id');
            $table->integer('quantity');
            $table->string('type');
            $table->integer('is_confirmed');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transfer_requests');
        Schema::dropIfExists('transfer_request_items');
        Schema::dropIfExists('transfer_request_item_batches');
        Schema::dropIfExists('transfer_request_dispatched_products');
        Schema::dropIfExists('transfer_request_waybills');
        Schema::dropIfExists('transfer_request_waybill_items');
    }
}
