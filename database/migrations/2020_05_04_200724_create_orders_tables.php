<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('warehouse_id')->nullable();
            $table->string('order_number');
            $table->string('order_status');
            $table->double('amount', 15, 4);
            $table->integer('currency_id');
            $table->integer('customer_id');
            $table->text('notes')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('warehouse_id');
            $table->unique(['warehouse_id', 'order_number', 'deleted_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id');
            $table->integer('item_id')->nullable();
            $table->double('quantity', 7, 2);
            $table->double('price', 15, 4);
            $table->double('total', 15, 4);
            $table->double('tax', 15, 4)->default('0.0000');
            $table->timestamps();
            $table->softDeletes();

        });

        Schema::create('order_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id');
            $table->date('paid_at');
            $table->double('amount', 15, 4);
            $table->string('currency_id');
            $table->text('description')->nullable();
            $table->string('payment_method');
            $table->string('reference')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });

        Schema::create('order_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id');
            $table->string('status_code');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });

        Schema::create('dispatched_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dispatcher_id');
            $table->unsignedInteger('order_id');
            $table->string('order_status_code')->default('pending');

            $table->foreign('order_id')->references('id')->on('orders')
                ->onUpdate('cascade')->onDelete('cascade');
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
        Schema::drop('orders');
        Schema::drop('order_items');
        Schema::drop('order_statuses');
        Schema::drop('order_payments');
        Schema::drop('order_histories');
        Schema::dropIfExists('dispatched_orders');
    }
}
