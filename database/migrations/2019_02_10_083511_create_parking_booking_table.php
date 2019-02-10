<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParkingBookingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * Booking ID will be the unique booking id for all transaction
         * Conversation ID will be like a session id that will be map to all transaction this will
         * be also used on audit logs
         * User ID will be the booking user id
         *
         */
        Schema::create('tbl_parking_booking', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_id'); //unique booking id
            $table->string('conv_id');
            $table->integer('user_id'); //must be unique
            $table->integer('vehicle_id'); //must be unique
            $table->integer('parking_space_id'); //must be unique
            $table->dateTime('booking_start');
            $table->dateTime('booking_end');
            $table->integer('parkspace_price_id');
            $table->enum('status', ['paid', 'cancelled', 'reserved', 'booked', 'declined']);
            $table->timestamps();
            //index
            $table->index('booking_id');
            $table->index('user_id', 'vehicle_id', 'parking_space_id');
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
        Schema::drop('tbl_parking_booking');
    }
}
