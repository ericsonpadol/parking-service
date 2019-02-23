<?php

use App\Copywrite;

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the controller to call when that URI is requested.
  |
 */

// Health Check
Route::get('/', function () {
    $cow = Cowsayphp\Farm::create(\Cowsayphp\Farm\Cow::class);
    echo '<pre>';
    echo '<b>PARKING SERVICE</b>';
    foreach ($_SERVER as $key => $spiels) {
        $cowResponse = $cow->say($key . ' => ' . $spiels);
        echo $cowResponse;
    }
    echo '</pre>';
});

//none token based routes
Route::get('user/verify', 'APIController@userVerify');
Route::post('user/security_question', 'UserController@storeSecurityQuestions');
Route::get('user/{id}/security_question', 'UserController@getSecurityQuestions');
Route::post('user/{id}/security_question', 'UserController@verifySecurityQuestions');
Route::resource('user_security', 'AccountSecurityController', ['only' => ['index', 'create']]);

/**
 * JWT Routing
 */
Route::group(['middleware' => ['api', 'secure.content'], 'prefix' => Copywrite::API_PREFIX], function() {
    Route::resource('api', 'APIController');
    Route::post('register', 'APIController@register');
    Route::post('reset_password', 'APIController@resetPassword');
    Route::post('login', 'APIController@login');
    Route::group(['middleware' => 'jwt-verify'], function() {
        Route::get('get_user_details', 'APIController@getAuthenticatedUser');
    });
});


/**
 *  Parking Service Routing
 */
Route::group(['middleware' => ['api'], 'prefix' => Copywrite::API_PREFIX], function() {
    Route::group(['middleware' => 'jwt-verify'], function() {
        Route::resource('parking_space', 'ParkingSpaceController', ['only' => ['index', 'show']]);
        Route::resource('user.parkingspace', 'UserParkingSpaceController', ['except' => 'create', 'edit']);
    });
});


/**
 * Profile Service Routing
 */
Route::group(['middleware' => ['api'], 'prefix' => Copywrite::API_PREFIX], function() {
    Route::group(['middleware' => 'jwt-verify'], function() {
        Route::resource('users', 'UserController', ['except' => ['store']]);
        Route::put('update_password', 'UserController@updatePassword');
    });
});

/**
 * Vehicle Routing
 */
Route::group(['middleware' => ['api'], 'prefix' => Copywrite::API_PREFIX], function() {
    Route::group(['middleware' => 'jwt-verify'], function() {
        Route::resource('vehicles', 'VehicleController', ['only' => 'index', 'show']);
        Route::resource('user.vehicle', 'UserVehicleController', ['except' => 'create', 'edit']);
    });
});


/*
 * Dingo Api Routes
 */

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function($api) {
    $api->get('test', function() {
        return 'Health Check';
    });
});

$api->version('v1', [], function($api) {
    $api->get('vehicles-dingo', 'App\Http\Controllers\VehicleController@index');
    $api->get('users-dingo', 'App\Http\Controllers\UserController@index');
    $api->post('register-user', 'App\Http\Controllers\APIController@register');
    $api->post('authenticate', 'App\Http\Controllers\APIController@login');
});
