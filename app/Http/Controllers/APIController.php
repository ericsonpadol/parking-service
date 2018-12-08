<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\User;
use Hash;
use Tymon\JWTAuth;

class APIController extends Controller {

    /**
     */
    public function register(Request $request) {
        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        User::create($input);
        return response()->json(['result' => true]);
    }

    /**
     */
    public function login(Request $request) {
        $input = $request->all();
        $token = JWTAuth::attemp($input);
        if (!$token) {
            return response()->json(['result' => 'invalid username and password']);
        }

        return response()->json(['result' => $token]);
    }

    /**
     */
    public function get_user_details(Request $request) {
        $input = $request->all();
        $user = JWTAuth::toUser($input['token']);
        return response()->json(['result' => $user]);
    }

}
