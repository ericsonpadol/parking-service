<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableParkingSpaces extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('parkingspaces', function (Blueprint $table) {
            $table->increments('id', 1);
            $table->text('image_uri')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('address');
            $table->string('city', 255);
            $table->integer('zipcode');
            $table->string('building_name');
            $table->float('space_lat', 10, 6);
            $table->float('space_lon', 10, 6);
            $table->enum('establishment_type', ['resident', 'commercial', 'public']);
            $table->text('description');
            $table->string('parking_slot', 255);
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')
                    ->references('id')
                    ->on('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('parkingspaces');
    }
}
