<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('company_name')->nullable();
            $table->string('representative_name')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('homepage_url')->nullable();
            $table->boolean('signed')->default(false);
            $table->string('manager_name')->nullable();
            $table->string('manager_email')->nullable();
            $table->string('manager_phone')->nullable();
            $table->string('person_in_charge_email')->nullable();
            $table->string('person_in_charge_name')->nullable();
            $table->string('person_in_charge_phone')->nullable();
            $table->dateTime('last_printed_date')->nullable();
            $table->integer('print_count')->default(0)->min(0);
            $table->integer('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('industry_id');
            $table->foreign('industry_id')->references('id')->on('lookups')->onDelete('cascade');
            $table->boolean('status')->default(true);
            $table->text('note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
