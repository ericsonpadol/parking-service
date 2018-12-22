<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\User;
use App\ParkingSpace;
use App\Copywrite;
use App\Http\Requests\CreateParkingSpaceRequest;
use Validator;

class UserParkingSpaceController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id) {
        $userAccount = User::find($id);

        if (!$userAccount) {
            return response()->json([
                        'message' => Copywrite::USER_NOT_FOUND,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'http_code' => Copywrite::HTTP_CODE_404
                            ], Copywrite::HTTP_CODE_404);
        }

        return response()->json([
                    'data' => $userAccount->parkingspaces,
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateParkingSpaceRequest $request, $userId) {
        $userAccount = User::find($userId);

        if (!$userAccount) {
            $message = [
                'code' => Copywrite::HTTP_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED,
                'message' => Copywrite::USER_NOT_FOUND,
            ];

            return response()->json(compact('message'));
        }

        $values = $request->all();

        $userAccount->parkingspaces()->create($values);

        return response()->json([
                    'message' => Copywrite::VEHICLE_CREATE_SUCCESS,
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                    'http_code' => Copywrite::HTTP_CODE_201
                        ], Copywrite::HTTP_CODE_201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $userId
     * @param  int $parkingSpaceId
     * @return \Illuminate\Http\Response
     */
    public function show($userId, $parkingSpaceId) {

        $userAccount = User::find($userId);

        if (!$userAccount) {
            return response()->json([
                        'message' => Copywrite::USER_NOT_FOUND,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'http_code' => Copywrite::HTTP_CODE_404
            ]);
        }

        $parkingSpace = $userAccount->parkingspaces->find($parkingSpaceId);

        if (!$parkingSpace) {
            return response()->json([
                        'message' => Copywrite::PARKING_SPACE_NOT_FOUND,
                        'http_code' => Copywrite::HTTP_CODE_404,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED
            ]);
        }

        return response()->json(['result' => [
                        'data' => $parkingSpace,
                        'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                        'http_code' => Copywrite::HTTP_CODE_200
                    ]
                        ], Copywrite::HTTP_CODE_200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $userid
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $userId, $parkingSpaceId) {
        //
        $userAccount = User::find($userId);

        if (!$userAccount) {
            return response()->json([
                        'message' => Copywrite::USER_NOT_FOUND,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'http_code' => Copywrite::HTTP_CODE_404
                            ], Copywrite::HTTP_CODE_404);
        }

        $parkingSpace = $userAccount->parkingspaces->find($parkingSpaceId);

        if (!$parkingSpace) {
            return response()->json([
                        'message' => Copywrite::PARKING_SPACE_NOT_FOUND,
                        'http_code' => Copywrite::HTTP_CODE_404,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED
            ]);
        }

        $values = $request->all();

        $validator = Validator::make($values, [
                    'address' => 'filled|string|max:255',
                    'city' => 'filled|string|max:255',
                    'space_lat' => 'numeric|filled',
                    'space_lon' => 'numeric|filled',
                    'establishment' => 'filled',
                    'parking_slot' => 'filled|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'message' => $validator->errors(),
                            ], Copywrite::HTTP_CODE_400);
        }

        $parkingSpace->update($values);

        return response()->json([
                    'messages' => Copywrite::DEFAULT_UPDATE_SUCCESS . ' ' . $request->get('id'),
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                    'http_code' => Copywrite::HTTP_CODE_200], Copywrite::HTTP_CODE_200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($userId, $parkingSpaceId) {
        //
        $userAccount = User::find($userId);

        if (!$userAccount) {
            return response()->json([
                        'message' => Copywrite::USER_NOT_FOUND,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'http_code' => Copywrite::HTTP_CODE_404
                            ], Copywrite::HTTP_CODE_404);
        }

        $parkingSpace = $userAccount->parkingspaces->find($parkingSpaceId);

        if (!$parkingSpace) {
            return response()->json([
                        'message' => Copywrite::PARKING_SPACE_NOT_FOUND,
                        'http_code' => Copywrite::HTTP_CODE_404,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED
            ]);
        }


        if (sizeof($parkingSpace->user) > 0) {
            return response()->json([
                        'message' => str_replace(':parkingspace:', $parkingSpace->parking_slot, Copywrite::PARKING_SPACE_DELETE_RESTRICT),
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'http_code' => Copywrite::HTTP_CODE_409
                            ], Copywrite::HTTP_CODE_409);
        }

        $parkingSpace->delete();

        return response()->json([
                    'message' => Copywrite::PARKING_SPACE_DELETE_ALLOWED,
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                    'http_code' => Copywrite::HTTP_CODE_200
                        ], Copywrite::HTTP_CODE_200);

    }

}
