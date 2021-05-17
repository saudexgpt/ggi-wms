<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnedProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('returned_products', function (Blueprint $table) {
            $table->id();
            $table->integer('warehouse_id');
            $table->string('item_id');
            $table->string('batch_no');
            $table->date('expiry_date')->nullable();
            $table->integer('quantity');
            $table->enum('reason', [
                'Product short-dated', 'Mass return', 'Rep. resignation/sack', 'Spillage', 'Others'])->default('Product short-dated');
            $table->string('other_reason')->nullable();
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
        Schema::dropIfExists('returned_products');
    }
}
