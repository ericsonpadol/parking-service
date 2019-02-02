<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\CustomQueryBuilder;

class User extends Authenticatable
{

    use SoftDeletes;

    protected $resetPasswordTable = 'reset_password';
    protected $resetPasswordColumns = [
        'email', 'reset_token'
    ];
    protected $date = [
        'deleted_at'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'password',
        'mobile_number',
        'full_name'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    /**
     * User & Vehicle Relationship
     */
    public function vehicles() {
        return $this->hasMany('App\Vehicle');
    }

    /**
     * User & Parkingspace Relationship
     */
    public function parkingspaces() {
        return $this->hasMany('App\ParkingSpace');
    }

    /**
     *
     */
    public function getUserVehicle($userId, $vehiclePlate) {
        $vehicle = User::find($userId)->vehicles()->where('plate_number', '=', $vehiclePlate)->get();

        return $vehicle;
    }
}
