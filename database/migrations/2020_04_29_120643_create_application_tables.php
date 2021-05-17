<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();

        });
        Schema::create('warehouses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('address');
            $table->boolean('enabled')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('warehouse_users', function (Blueprint $table) {
            $table->unsignedInteger('warehouse_id');
            $table->unsignedInteger('user_id');

            $table->foreign('warehouse_id')->references('id')->on('warehouses')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['user_id', 'warehouse_id']);
        });
        Schema::create('currencies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code');
            $table->double('rate', 15, 8);
            $table->tinyInteger('enabled')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['code', 'deleted_at']);
        });
        Schema::create('taxes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->double('rate', 15, 4);
            $table->boolean('enabled');
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
        Schema::dropIfExists('categories');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('taxes');
        Schema::dropIfExists('warehouse_users');


    }
}
