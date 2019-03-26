<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ParkingSpace;
use App\Http\Requests;
use App\Copywrite;
use Validator;

class ParkingSpaceController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //
        $parkingSpaces = ParkingSpace::all();

        return response()->json([
            'data' => $parkingSpaces,
            'status_code' => Copywrite::STATUS_CODE_200,
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
    public function store(Request $request) {
        //
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

    /**
     * this method will return the nearby parking spaces with a radius of 5 KM
     * @var Request $request
     * @return Mixed
     */
    public function getNearbyParkingSpace(Request $request) {
        $parkingSpace = new ParkingSpace();

        $validator = Validator::make($request->all(), [
            'currentLat' => 'required',
            'currentLong' => 'required'
        ]);

        $params = [
            'fromLat' => $request->only(['currentLat']),
            'fromLon' => $request->only(['currentLong'])
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
        ], Copywrite::HTTP_CODE_200);
    }
}
