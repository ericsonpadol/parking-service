<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Copywrite;
use App\CustomQueryBuilder;
use Tymon\JWTAuth\Claims\Custom;
use DB;
use Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\CustomLogger;
use FarhanWazir\GoogleMaps\GMaps;


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

    private $_logger = '';

    /**
     * parking space constructor
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        DB::connection()->enableQueryLog();
        $this->_logger = new Logger('ParkingSpace');
        $this->_logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function parkingspacepricing()
    {
        return $this->hasOne('App\ParkingSpacePrice');
    }

    public function vehicles()
    {
        return $this->hasMany('App\Vehicles');
    }

    /***
     * this method will list down the list of nearby parking space
     *
     * @return Array $params
     * @return Mixed
     */
    public function getNearbyParkingSpace($params = [])
    {
        /**
         * Note: @EBP you might want to refactor this in the near future since we do not want to use
         * raw sqls, we might find an ORM work around | 03252019
         */
        $sql = new CustomQueryBuilder();

        //expected query parameters
        $sqlQuery = $sql->getNearbyParkingSpaces($params['fromLat'], $params['fromLon']);

        $result = DB::select(DB::raw($sqlQuery));

        //application log
        Log::info(CustomLogger::getConversationId() .
            CustomLogger::getCurrentRoute() .
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));
        Log::info(CustomLogger::getConversationId() .
            CustomLogger::getCurrentRoute() .
            CustomLogger::RESULT . serialize($result));

        //stream logging
        $this->_logger->addInfo(CustomLogger::getConversationId() .
            CustomLogger::getCurrentRoute() .
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));
        $this->_logger->addInfo(CustomLogger::getConversationId() .
            CustomLogger::getCurrentRoute() .
            CustomLogger::RESULT . serialize($result));

        return $result;
    }

    /**
     * test map function
     * @param Array $config
     * @param Array $markers
     * @return Mixed $map
     */
    public function testMap(array $config, array $markers)
    {
        $gmaps = new GMaps();

        $gmaps->initialize($config);

        //create 10 markers
        foreach($markers as $marker) {
            $gmaps->add_marker($marker);
        }

        //create map
        $map = $gmaps->create_map();

        return $map;
    }
}
