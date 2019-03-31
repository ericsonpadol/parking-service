<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Vehicle;
use App\Http\Requests;
use App\Copywrite;

class VehicleController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $vehicles = Vehicle::all();

        return response()->json(['data' => $vehicles], 200);
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
    public function show($vehiclePlate) {
        $vehicles = Vehicle::where('plate_number', '=', $vehiclePlate)->get();

        if (!$vehicles || $vehicles->isEmpty()) {

            //returning empty array
            $response = response()->json([
                'message' => [],
                'http_code' => Copywrite::HTTP_CODE_200,
                'status' => Copywrite::RESPONSE_STATUS_SUCCESS
            ], Copywrite::HTTP_CODE_200);

        } else {

            $response = response()->json([
                'data' => $vehicles,
                'http_code' => Copywrite::HTTP_CODE_200,
                'status' => Copywrite::RESPONSE_STATUS_SUCCESS
            ], Copywrite::HTTP_CODE_200);

        }

        return $response;
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
