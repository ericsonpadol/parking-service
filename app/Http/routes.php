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
    return '<pre>'.$cow->say('Parking Service API').'</pre>';
});

/**
 * JWT Routing
 */
/*Route::group(['middleware' => ['api', 'cors'], 'prefix' => 'service'], function() {
    Route::resource('api', 'APIController');
    Route::post('register', 'ApiController@register');
    Route::post('login', 'APIController@login');
    Route::group(['middleware' => 'jwt-auth'], function() {
        Route::post('get_user_details', 'APIController@get_user_details');
    });
});*/
Route::post('user/register', 'APIRegisterController@register');
Route::post('user/login', 'APILoginController@login');

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
        return 'firing dingo';
    });
});

$api->version('v1', [], function($api) {
    $api->get('vehicles-dingo', 'App\Http\Controllers\VehicleController@index');
});
