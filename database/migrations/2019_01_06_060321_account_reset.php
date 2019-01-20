<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AccountReset extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('reset_password', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->index();
            $table->string('reset_token')->index();
            $table->enum('activation', ['0', '1'])->default('0');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('reset_password');
    }

}
