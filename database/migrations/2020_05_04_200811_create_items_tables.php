<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id');
            $table->string('name');
            $table->string('sku');
            $table->text('description')->nullable();
            $table->string('picture')->nullable();
            $table->boolean('enabled');
            $table->timestamps();
            $table->softDeletes();


            $table->foreign('category_id')->references('id')->on('categories')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->unique(['sku']);
        });

        Schema::create('item_stocks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('warehouse_id');
            $table->unsignedInteger('item_id');
            $table->double('sale_price', 15, 4);
            $table->double('purchase_price', 15, 4);
            $table->integer('currency_id');
            $table->integer('quantity');
            $table->integer('tax_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('warehouse_id')->references('id')->on('warehouses')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('item_id')->references('id')->on('items')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->index('currency_id');
            $table->index('warehouse_id');
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
        Schema::dropIfExists('items');
        Schema::dropIfExists('item_stocks');
    }
}
