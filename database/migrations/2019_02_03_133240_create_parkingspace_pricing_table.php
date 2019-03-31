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
            $table->double('pspace_base_price');
            $table->double('pspace_calc_price')->nullable();
            $table->dateTime('avail_start_datetime');
            $table->dateTime('avail_end_datetime');
            $table->integer('parking_space_id')
                ->unsigned();
            $table->integer('user_id')->unsigned();
            $table->foreign('parking_space_id')
                ->references('id')
                ->on('parkingspaces');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('parkingspaces');
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
