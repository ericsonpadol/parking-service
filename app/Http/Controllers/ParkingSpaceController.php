<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ParkingSpace;
use App\Http\Requests;
use App\Copywrite;
use Validator;
use Session;
use DB;
use Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\CustomLogger;
use function GuzzleHttp\json_decode;

class ParkingSpaceController extends Controller
{

    private $_logger = '';

    public function __construct()
    {
        DB::connection()->enableQueryLog();
        $this->_logger = new Logger('ParkingSpaceController');
        $this->_logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $parkingSpaces = ParkingSpace::all();

        //log
        Log::info(CustomLogger::getCurrentRoute() .
            CustomLogger::getConversationId() .
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));

        Log::info(CustomLogger::getCurrentRoute() .
            CustomLogger::getConversationId() .
            CustomLogger::RESULT . serialize($parkingSpaces));

        $this->_logger->addInfo(CustomLogger::getCurrentRoute() .
            CustomLogger::getConversationId() .
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));

        $this->_logger->addInfo(CustomLogger::getCurrentRoute() .
            CustomLogger::getConversationId() .
            CustomLogger::RESULT . serialize($parkingSpaces));

        return response()->json([
            'data' => $parkingSpaces,
            'status_code' => Copywrite::STATUS_CODE_200,
            'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
            'http_code' => Copywrite::HTTP_CODE_200
        ], Copywrite::HTTP_CODE_200)
            ->header(Copywrite::HEADER_CONVID, Session::getId());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * this method will return the nearby parking spaces with a radius of 5 KM
     * @var Request $request
     * @return Mixed
     */
    public function getNearbyParkingSpace(Request $request)
    {
        $parkingSpace = new ParkingSpace();

        $validator = Validator::make($request->all(), [
            'currentLat' => 'required',
            'currentLong' => 'required'
        ]);

        $params = [
            'fromLat' => $request->currentLat,
            'fromLon' => $request->currentLong
        ];

        if ($validator->fails()) {
            //this coordinates will default to uptown mall bgc.
            $params = [
                'fromLat' => '14.5564973',
                'fromLon' => '121.0520231'
            ];
        }

        $markers = $parkingSpace->getNearbyParkingSpace($params);

        $pspaceNearby = $markers ? $markers : Copywrite::NO_NEARBY_PARKINGSPACES;

        return response()->json([
            'data' => $pspaceNearby,
            'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
            'http_code' => Copywrite::HTTP_CODE_200,
            'status_code' => Copywrite::STATUS_CODE_200
        ], Copywrite::HTTP_CODE_200)
            ->header(Copywrite::HEADER_CONVID, Session::getId());
    }

    /**
     * test nearby map
     * @param Request $request
     * @return View Html
     */
    public function testMap(Request $request) {
        $parkingSpace = new ParkingSpace();

        //sample params
        $params = [
            'fromLat' => $request->currentLat,
            'fromLon' => $request->currentLon
        ];

        $nearbyParkingSpaces = $parkingSpace->getNearbyParkingSpace($params);

        $config = [
            'center' => $params['fromLat'] . ',' . $params['fromLon'],
            'zoom' => 16,
            'map_width' => '800px',
            'cluster' => false,
            'geocodeCaching' => true,
            // 'minifyJS' => true
        ];

        //markers limit to 10
        $marker = [];
        foreach($nearbyParkingSpaces as $key => $value) {
            //map title
            $mapTitleReplaceTo = ['/:map_parkingslot:/', '/:map_calcprice:/'];
            $mapTitleReplaceFrom = [$value->parking_slot, $value->pspace_calc_price];
            $mapTitle = preg_replace($mapTitleReplaceTo, $mapTitleReplaceFrom, Copywrite::MAP_TITLE);

            //marker infowindow content
            $infoReplaceTo = ['/:map_buildingname:/', '/:map_address:/', '/:map_parkingslot:/', '/:map_calcprice:/', '/:map_distance:/'];
            $infoReplaceFrom = [$value->building_name, $value->address,
                $value->parking_slot, $value->pspace_calc_price, round($value->distance, 3)];
            $markerInfo = preg_replace($infoReplaceTo, $infoReplaceFrom, Copywrite::MAP_INFOWINDOW_CONTENT);

            $marker[$key] = [
                'position' => $value->space_lat . ',' . $value->space_lon,
                'animation' => 'DROP',
                'title' => $mapTitle,
                'infowindow_content' => $markerInfo
            ];
        }

        return view('welcome')->with('map', $parkingSpace->testMap($config, $marker));
    }

    /**
     * search for parking space using a wildcard, coordinates should be passed
     * @param Request $request
     * @return Mixed
     */
    public function findParkingSpace(Request $request) {
        $parkingSpace = new ParkingSpace();

        $validator = Validator::make($request->all(), [
            'currentLat' => 'required',
            'currentLon' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'http_code' => Copywrite::HTTP_CODE_422,
                'status_code' => Copywrite::STATUS_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_422)
                ->header(Copywrite::HEADER_CONVID, Session::getId());
        }

        $params = [
            'wildcard' => $request->wildcard,
            'fromLat' => $request->currentLat,
            'fromLon' => $request->currentLon
        ];

        $data = $parkingSpace->findParkingSpace($params);

        return response()->json([
            'data' => $data,
            'http_code' => Copywrite::HTTP_CODE_200,
            'status_code' => Copywrite::HTTP_CODE_200,
            'status' => Copywrite::RESPONSE_STATUS_SUCCESS
        ], Copywrite::HTTP_CODE_200)
            ->header(Copywrite::HEADER_CONVID, Session::getId());
    }

    /**
     *this method will return the selected parking space
     *@param Integer $parkingspace
     *@return Response
     */
    public function getSelectedParkingSpace($parkspace) {
        $parkingSpace = new ParkingSpace();
        $result = $parkingSpace->getSelectedParkingSpace($parkspace);

        return response()->json($result, Copywrite::HTTP_CODE_200)->header(Copywrite::HEADER_CONVID, Session::getId());
    }

    /**
     * test distance mapping
     */
    public function testDistanceMapping(Request $request) {
        $parkingSpace = new ParkingSpace();
        $result = $this->getSelectedParkingSpace($request->id);
        $data = json_decode($result->content());

        //map config
        $config = [
            'center' => $data->data->space_lat . ',' . $data->data->space_lon,
            'zoom' => 16,
            'map_width' => '800px',
            'directions' => true,
            'directionsStart' => $request->fromLat . ', ' . $request->fromLon,
            'directionsEnd' => $data->data->space_lat . ',' . $data->data->space_lon,
            'directionsMode' => 'DRIVING',
            'directionsDivID' => 'directions',
        ];

        return view('welcome')->with('map', $parkingSpace->testMap($config));
    }
}
