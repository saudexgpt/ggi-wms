<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemStockSubBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_stock_sub_batches', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('item_stock_id');
            $table->string('batch_no');
            $table->integer('quantity');
            $table->integer('in_transit');
            $table->integer('supplied');
            $table->integer('balance');
            $table->string('goods_received_note');
            $table->date('expiry_date');
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
        Schema::dropIfExists('item_stock_sub_batches');
    }
}
