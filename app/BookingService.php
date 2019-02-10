<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class BookingService extends Model
{
    protected $table = 'tbl_parking_booking';

    public function createBooking(array $params)
    {
        $bookingParams = [];
        /**
         * Check the if the booking exists already
         */

        $bookingFound = $this->checkBooking();

    }

    public function checkBooking(array $params)
    {

    }
}
