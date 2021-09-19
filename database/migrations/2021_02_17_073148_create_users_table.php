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
            $table->timestamps();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('user_name')->unique();
            $table->string('company_name')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('password');
            $table->string('url_image')->nullable();
            $table->json('tokens')->nullable();
            $table->dateTime('locked_at')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->integer('failed_login_time')->default(0)->nullable();
            $table->integer('role_id')->nullable();
            $table->foreign('role_id')->references('id')->on('roles');
            $table->boolean('active')->default(true);
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
    }
}
