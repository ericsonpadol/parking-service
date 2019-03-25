<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Copywrite;
use App\CustomQueryBuilder;
use Tymon\JWTAuth\Claims\Custom;
use DB;

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
        'parking_slot',
        'image_uri'
    ];
    protected $hidden = [
        'created_at',
    ];

    public function user() {
        return $this->belongsTo('App\User');
    }

    /***
     * this method will list down the list of nearby parking space
     * @var Array $params
     * @return Array
     */
    public function getNearbyParkingSpace($params = []) {
        $sql = new CustomQueryBuilder();
        $params = [
            'fromLat' => '-21.430013',
            'fromLon' => '118.096038',
        ];

        $test = $sql->getNearbyParkingSpaces($params['fromLat'], $params['fromLon']);
        $a = collect(DB::select(DB::raw($test)));

        print_r($a);
    }
}
