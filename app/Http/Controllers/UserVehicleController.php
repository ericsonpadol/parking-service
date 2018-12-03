<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserVehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $user = User::find($id);

        $response = !$user ?
                response()->json(['message' => 'user not found!', 'code' => '404'], 404) :
                response()->json(['data' => $user->vehicles], 200);

        return $response;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($userId, $vehiclePlate)
    {
        $oUser = new User();
        $user = $oUser->getUserVehicle($userId, $vehiclePlate);

        $response = !$user || $user->isEmpty() ?
            response()->json(['message' => 'record not found!', 'code' => '404'], 404) :
            response()->json([
                'data' => $user,
            ], 200);

        return $response;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
