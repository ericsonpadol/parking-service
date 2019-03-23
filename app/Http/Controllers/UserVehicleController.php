<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Vehicle;
use App\Http\Requests\CreateVehicleRequest;
use App\Copywrite;
use Validator;
use Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use DB;

class UserVehicleController extends Controller
{
    //configurations
    private $_logger = '';

    public function __construct()
    {
        DB::connection()->enableQueryLog();
        $this->_logger = new Logger('vehicles-update');
        $this->_logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id) {
        $userAccount = User::find($id);

        if (!$userAccount) {
            return response()->json([
                        'messages' => Copywrite::USER_NOT_FOUND,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'http_code' => Copywrite::HTTP_CODE_404
                            ], Copywrite::HTTP_CODE_404);
        }

        if ($userAccount->vehicles->isEmpty()) {
            return response()->json([
                'message' => Copywrite::VEHICLE_NOT_FOUND,
                'status' => Copywrite::RESPONSE_STATUS_FAILED,
                'http_code' => Copywrite::HTTP_CODE_404
            ], Copywrite::HTTP_CODE_404);
        }

        return response()->json([
                    'data' => $userAccount->vehicles,
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                    'http_code' => Copywrite::HTTP_CODE_200
                        ], Copywrite::HTTP_CODE_200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $userId) {
        $useraccount = User::find($userId);

        if (!$useraccount) {
            $messages = [
                'code' => Copywrite::HTTP_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED,
                'messages' => Copywrite::USER_NOT_FOUND,
            ];

            return response()->json(compact('messages'));
        }

        $validator = Validator::make($request->all(), [
            'plate_number' => 'required|unique:vehicles|alpha_num|max:7|min:6',
            'color' => 'required',
            'model' => 'required',
            'brand' => 'required',
            'image_uri' => 'url'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'http_code' => Copywrite::HTTP_CODE_422,
                'status_code' => Copywrite::STATUS_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_422);
        }

        $values = $request->all();

        $useraccount->vehicles()->create($values);

        return response()->json([
                    'message' => Copywrite::VEHICLE_CREATE_SUCCESS,
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                    'http_code' => Copywrite::HTTP_CODE_201
                        ], Copywrite::HTTP_CODE_201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($userId, $vechicleId) {
        //check if user is available
        $found = User::find($userId);

        if (!$found) {
            return response()->json([
                'message' => Copywrite::USER_NOT_FOUND,
                'http_code' => Copywrite::HTTP_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_404);
        }

        $oUser = new User();
        $response = $oUser->getUserVehicle($userId, $vechicleId);

        return $response;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $userId, $vehicleId) {
        $useraccount = User::find($userId);

        if (!$useraccount) {
            $messages = [
                'code' => Copywrite::HTTP_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED,
                'message' => Copywrite::USER_NOT_FOUND,
            ];

            return response()->json(compact('messages'));
        }

        $values = $request->all();

        //get request
        $vehicle = $useraccount->vehicles->find($vehicleId);

        if (!$vehicle) {
            return response()->json([
                        'messages' => Copywrite::VEHICLE_NOT_FOUND,
                        'http_code' => Copywrite::HTTP_CODE_404,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED
            ]);
        }

        $validator = Validator::make($values, [
            'plate_number' => 'string|max:11|alpha_num|unique:vehicles,plate_number|filled',
            'color' => 'string|max:255|filled',
            'model' => 'string|max:255|filled',
            'brand' => 'string|max:255|filled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'http_code' => Copywrite::HTTP_CODE_422,
                'status_code' => Copywrite::STATUS_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_422);
        }

        $vehicle->update($values);

        //stream logging
        $this->_logger->addInfo('Request: ' . serialize($values));
        $this->_logger->addInfo('Query: ' . serialize(DB::getQueryLog()));

        return response()->json([
                    'messages' => Copywrite::DEFAULT_UPDATE_SUCCESS . ' ' . $request->get('id'),
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                    'http_code' => Copywrite::HTTP_CODE_200], Copywrite::HTTP_CODE_200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($userId, $vehicleId) {
        $userAccount = User::find($userId);

        if (!$userAccount) {
            return response()->json([
                        'messages' => Copywrite::USER_NOT_FOUND,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'http_code' => Copywrite::HTTP_CODE_404
                            ], Copywrite::HTTP_CODE_404);
        }

        $vehicle = $userAccount->vehicles->find($vehicleId);

        if (!$vehicle) {
            return response()->json([
                        'messages' => Copywrite::VEHICLE_NOT_FOUND,
                        'http_code' => Copywrite::HTTP_CODE_404,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED
            ]);
        }

        //replace this with booking transactions
//        if (sizeof($vehicle->user) > 0) {
//            return response()->json([
//                        'messages' => str_replace(':parkingspace:', $parkingSpace->parking_slot, Copywrite::VEHICLE_DELETE_RESTRICT),
//                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
//                        'http_code' => Copywrite::HTTP_CODE_409
//                            ], Copywrite::HTTP_CODE_409);
//        }

        $vehicle->delete();

        return response()->json([
                    'messages' => Copywrite::VEHICLE_DELETE_ALLOWED,
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                    'http_code' => Copywrite::HTTP_CODE_200
                        ], Copywrite::HTTP_CODE_200);
    }

}
