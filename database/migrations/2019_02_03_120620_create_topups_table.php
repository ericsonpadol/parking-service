<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTopupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //run
        Schema::create('topups', function(Blueprint $table) {
            $table->increments('id');
            $table->string('topup_key');
            $table->string('topup_value');
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
        //run
        Schema::drop('topups');
    }
}
