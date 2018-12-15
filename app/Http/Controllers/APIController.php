<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\RegisterNewUserRequest;
use App\Http\Requests\LoginAuthenticateRequest;

class APIController extends Controller
{

    public function register(RegisterNewUserRequest $request) {
        $userInput = $request->only([
            'email',
            'full_name',
            'password',
            'mobile_number',
        ]);

        $userInput['password'] = password_hash($userInput['password'], PASSWORD_DEFAULT);

        User::create($userInput);
        $user = User::first();
        $token = JWTAuth::fromUser($user);
        return response()->json(['token' => $token]);
    }

    public function login(LoginAuthenticateRequest $request) {
        $userInput = $request->only([
            'email',
            'password'
        ]);

        if (!$token = JWTAuth::attempt($userInput)) {
            return response()->json([
                        'result' => 'wrong email or password'
            ]);
        }

        return response()->json(compact('token'));
    }

    public function getAuthenticatedUser() {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(
                ['token_expired'], $e->getStatusCode()
            );
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(
                ['token_invalid'], $e->getStatusCode()
            );
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(
                ['token_absent'], $e->getStatusCode()
            );
        }

        return response()->json(compact('user'));
    }

}
