<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('warehouse_id')->nullable();
            $table->string('invoice_number');
            $table->integer('customer_id');
            $table->dateTime('invoice_date');
            $table->string('status');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('warehouse_id');
            $table->unique(['warehouse_id', 'invoice_number', 'deleted_at']);
        });
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('invoice_id');
            $table->integer('item_id')->nullable();
            $table->double('quantity', 7, 2);
            $table->string('type');
            $table->double('price', 15, 4);
            $table->double('total', 15, 4);
            $table->double('tax', 15, 4)->default('0.0000');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('waybills', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('invoice_id');
            $table->string('waybill_no')->unique();
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('invoice_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('invoice_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('invoice_id');
            $table->string('status');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dispatched_waybills', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('driver_id');
            $table->unsignedInteger('waybill_id');
            $table->string('status')->default('pending');

            $table->foreign('waybill_id')->references('id')->on('waybills')
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
        Schema::drop('invoices');
        Schema::drop('invoice_items');
        Schema::drop('waybills');
        Schema::drop('invoice_statuses');
        Schema::drop('invoice_histories');
        Schema::dropIfExists('dispatched_waybills');
    }
}
