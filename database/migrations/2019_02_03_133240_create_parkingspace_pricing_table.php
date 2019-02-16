<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateParkingspacePricingTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        //run
        Schema::create('parkspace_pricing', function (Blueprint $table) {
            $table->increments('id');
            $table->double('pspace_price');
            $table->dateTime('avail_start_datetime');
            $table->dateTime('avail_end_datetime');
            $table->integer('parking_space_id');
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        //run
        Schema::drop('parkspace_pricing');
    }
}
