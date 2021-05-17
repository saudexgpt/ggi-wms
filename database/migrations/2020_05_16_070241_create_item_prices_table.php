<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_id');
            $table->double('sale_price', 15, 4);
            $table->double('purchase_price', 15, 4);
            $table->integer('currency_id');
            $table->softDeletes();
            $table->timestamps();


            $table->foreign('item_id')->references('id')->on('items')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->index('currency_id');
            $table->index('item_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_prices');
    }
}
