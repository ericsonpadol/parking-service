<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParkingSpace extends Model
{
    use SoftDeletes;
    //configuration
    protected $data = ['deleted_at'];
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

    public function user() {
        return $this->belongsTo('App\User');
    }
}
