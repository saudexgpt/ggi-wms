<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemTax extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_tax', function (Blueprint $table) {
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('tax_id');
            $table->timestamps();
            $table->foreign('item_id')->references('id')->on('items')
                ->onUpdate('cascade')->onDelete('cascade');


            $table->primary(['tax_id', 'item_id']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_tax');
    }
}
