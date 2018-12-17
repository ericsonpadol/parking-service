<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ParkingSpace extends Model
{

    //configuration
    protected $table = 'parkingspaces';
    protected $primaryKey = 'id';
    protected $fillable = [
        'address',
        'city',
        'zipcode',
        'building_name',
        'space_lat',
        'space_lon',
        'establishment_type',
        'description',
        'user_id',
        'parking_slot'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function parkingspace() {
        return $this->belongsTo('App\User');
    }

}
