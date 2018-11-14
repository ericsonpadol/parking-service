<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'password',
        'mobile_number'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'deleted_at'
    ];

    public function vehicles() {
        return $this->hasMany('App\Vehicle');
    }

    public function getUserVehicle($userId, $vehiclePlate) {
        $vehicle = User::find($userId)->vehicles()->where('plate_number', '=', $vehiclePlate)->get();

        return $vehicle;
    }

}
