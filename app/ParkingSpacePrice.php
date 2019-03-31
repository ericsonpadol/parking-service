<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\ParkingSpace;

class ParkingSpacePrice extends Model
{
    //configurations
    protected $table = 'parkspace_pricing';

    protected $fillable = [
        'pspace_base_price',
        'pspace_calc_price',
        'avail_start_datetime',
        'avail_end_datetime',
        'parking_space_id',
        'user_id'
    ];

    protected $hidden = [
        'created_at'
    ];


    public function parkingspace() {
        return $this->belongsTo('App\ParkingSpace');
    }

}
