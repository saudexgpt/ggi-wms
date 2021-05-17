<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('disabled_users', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->timestamps();
        });

        Schema::create('dispatchers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('warehouse_id');
            $table->integer('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('warehouse_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('dispatchers');
        Schema::dropIfExists('disabled_users');
    }
}
