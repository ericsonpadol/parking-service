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
                    'message' => Copywrite::PARKING_SPACE_CREATE_SUCCESS,
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

}
