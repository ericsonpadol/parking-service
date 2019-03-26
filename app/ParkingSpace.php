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
     *
     * @return Array $params
     * @return Mixed
     */
    public function getNearbyParkingSpace($params = []) {
        /**
         * Note: @EBP you might want to refactor this in the near future since we do not want to use
         * raw sqls, we might find an ORM work around | 03252019
         */
        $sql = new CustomQueryBuilder();

        //expected query parameters
        $sqlQuery = $sql->getNearbyParkingSpaces($params['fromLat'], $params['fromLon']);

        $result = DB::select(DB::raw($sqlQuery));

        return $result;
    }
}
