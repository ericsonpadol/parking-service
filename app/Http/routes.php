<?php

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

Route::get('/', function () {
    $cow = Cowsayphp\Farm::create(\Cowsayphp\Farm\Cow::class);
    return '<pre>'.$cow->say('Parking Service API Health Check').'</pre>';
});

/**
 * JWT Routing
 */
Route::group(['middleware' => ['api'], 'prefix' => 'latest-api'], function() {
    Route::resource('api', 'APIController');
    Route::post('register', 'ApiController@register');
    Route::post('login', 'APIController@login');
    Route::group(['middleware' => 'jwt-verify'], function() {
        Route::get('get_user_details', 'APIController@getAuthenticatedUser');
    });
});


/**
 * Default Resource Routing
 */
Route::resource('vehicles', 'VehicleController', ['only' => ['index', 'show']]);
Route::resource('subscribers', 'SubscriberController', ['only' => ['index', 'show']]);
Route::resource('users', 'UserController', ['except' => ['store']]);
Route::resource('users.vehicles', 'UserVehicleController');

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
    $api->post('register-user', 'App\Http\Controllers\ApiController@register');
    $api->post('authenticate', 'App\Http\Controllers\APIController@login');
});