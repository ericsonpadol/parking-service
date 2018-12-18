<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\ParkingSpace;
use App\Vehicle;
use App\Copywrite;
use App\Http\Requests;
use Validator;

class UserController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $users = User::all();

        return response()->json(['data' => $users], 200);
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
    public function store(Request $request) {
        /**
         * Note:
         * That new user registration are found on the APIController
         */
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        //
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
    public function update(Request $request, $userId) {
        $userAccount = User::find($userId);

        if (!$userAccount) {
            return response()->json([
                        'message' => Copywrite::USER_NOT_FOUND,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'http_code' => Copywrite::HTTP_CODE_404
                            ], Copywrite::HTTP_CODE_404);
        }

        $values = $request->except(['password', 'email']);

        $validator = Validator::make($values, [
                    'email' => 'email|max:255|unique:users,email',
                    'mobile_number' => 'min:11|max:11|unique:users,mobile_number',
                    'full_name' => 'string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'message' => $validator->errors(),
                            ], Copywrite::HTTP_CODE_400);
        }

        $userAccount->update($values);

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
    public function destroy($id) {
        $userAccount = User::find($id);

        if (!$userAccount) {
            return response()->json([
                        'message' => Copywrite::USER_NOT_FOUND,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'http_code' => Copywrite::HTTP_CODE_404
                            ], Copywrite::HTTP_CODE_404);
        }

        //validate user has no transactions
        $parkingspaces = $userAccount->parkingspaces;
        $vehicles = $userAccount->vehicles;

        if (sizeof($parkingspaces) > 0 && sizeof($vehicles) > 0) {
            return response()->json([
                        'message' => str_replace(':useraccount:', $userAccount->email, Copywrite::USER_DELETE_RESTRICT),
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'http_code' => Copywrite::HTTP_CODE_409
                            ], Copywrite::HTTP_CODE_409);
        }

        $userAccount->delete();

        return response()->json([
                    'message' => Copywrite::USER_DELETE_ALLOWED,
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                    'http_code' => Copywrite::HTTP_CODE_200
                        ], Copywrite::HTTP_CODE_200);
    }

}
