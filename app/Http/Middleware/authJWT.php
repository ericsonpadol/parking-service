<?php

namespace App\Http\Middleware;

use Closure;

class authJWT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try{
            $user = JWTAuth::toUser($request);
        }catch(Exception $e){
            //JWT breakdown check if the api authtenticates
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['error' => 'Invalid Token']);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['error' => 'Expired Token']);
            } else {
                return response()->json(['error' => 'Something Went Wrong!']);
            }
        }
        return $next($request);
    }
}
