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
    public function testMap(array $config, $markers = [], $polylines = [])
    {
        $gmaps = new GMaps();

        $gmaps->initialize($config);

        //create 10 markers
        if ($markers) {
            foreach($markers as $marker) {
                $gmaps->add_marker($marker);
            }
        }


        //create polylines
        if ($polylines) {
            foreach($polylines as $polyline) {
                $gmaps->add_polyline($polyline);
            }
        }

        //create map
        $map = $gmaps->create_map();

        return $map;
    }

    /**
     * find the selected parking space
     * wildcard are address and city return top 20 from the list
     * order by rating and distance
     * @param Array $params
     * @return Object
     */
    public function findParkingSpace($params = []) {
        $joinTable = 'parkspace_pricing';

        $result = DB::table($this->table)
            ->join($joinTable, $this->table . '.id', '=', $joinTable . '.parking_space_id')
            ->select(
                $this->table . '.id',
                $this->table . '.address',
                $this->table . '.city',
                $this->table . '.building_name',
                $this->table . '.space_lat',
                $this->table . '.space_lon',
                $this->table . '.establishment_type',
                $this->table . '.ratings',
                $this->table . '.parking_slot',
                $this->table . '.ratings',
                $joinTable . '.user_id',
                $joinTable . '.pspace_calc_price',
                $joinTable . '.avail_start_datetime',
                $joinTable . '.avail_end_datetime',
                DB::raw(CustomQueryBuilder::getParkingSpaceDistance($params['fromLat'], $params['fromLon'])))
            ->where([
                    [$this->table . '.status', '=', 'active'],
                    [$this->table . '.address', 'like', '%' . $params['wildcard'] . '%'],
                ])
            ->orWhere($this->table . '.city', 'like', '%' . $params['wildcard'] . '%')
            ->orWhere($this->table . '.building_name', 'like', '%' . $params['wildcard'] . '%')
            ->orderBy('ratings', 'desc')
            ->orderBy('distance', 'asc')
            ->orderBy($joinTable . '.pspace_calc_price', 'asc')
            ->having($this->table . '.ratings', '>', 1)
            ->take(20)
            ->get();

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

        return $result ? $result : [];
    }

    /**
     * get selected parking space info
     * @param Integer $parkspace
     * @return Object
     */
    public function getSelectedParkingSpace($parkspace) {
        $parkingSpace = ParkingSpace::find($parkspace);

        if (!$parkingSpace) {
            return [
                'message' => Copywrite::PARKING_SPACE_NOT_FOUND,
                'http_code' => Copywrite::HTTP_CODE_404,
                'status_code' => Copywrite::STATUS_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ];
        }

        $result = $parkingSpace->parkingspacepricing;

        if (!$result) {
            return [
                'message' => Copywrite::PARKING_SPACE_NOT_FOUND,
                'http_code' => Copywrite::HTTP_CODE_404,
                'status_code' => Copywrite::STATUS_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ];
        }

        $psObj = collect([
            'image_uri' => $parkingSpace->image_uri,
            'status' => $parkingSpace->status,
            'address' => $parkingSpace->address,
            'city' => $parkingSpace->city,
            'zipcode' => $parkingSpace->zipcode,
            'building_name' => $parkingSpace->building_name,
            'space_lat' => $parkingSpace->space_lat,
            'space_lon' => $parkingSpace->space_lon,
            'establishment_type' => $parkingSpace->establishment_type,
            'description' => $parkingSpace->description,
            'ratings' => $parkingSpace->ratings,
            'parking_slot' => $parkingSpace->parking_slot
        ]);

        $pspObj = collect([
            'pspace_calc_price' => $result->pspace_calc_price,
            'avail_start_datetime' => $result->avail_start_datetime,
            'avail_end_datetime' => $result->avail_end_datetime,
            'parking_space_id' => $result->parking_space_id,
            'user_id' => $result->user_id
        ]);

        $merged = $psObj->merge($pspObj);

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

        return [
            'data' => $merged['status'] === 'active' ? $merged->all() : [],
            'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
            'http_code' => Copywrite::HTTP_CODE_200,
            'status_code' => Copywrite::STATUS_CODE_200
        ];
    }
}
