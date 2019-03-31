<?php

use App\Copywrite;
use FarhanWazir\GoogleMaps\GMaps;

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
Route::get('/app.info', function () {
    $cow = Cowsayphp\Farm::create(\Cowsayphp\Farm\Cow::class);
    echo '<pre>';
    echo '<b>PARKING SERVICE</b>';
    foreach ($_SERVER as $key => $spiels) {
        $cowResponse = $cow->say($key . ' => ' . $spiels);
        echo $cowResponse;
    }
    echo '</pre>';
});

Route::get('/test-map', function() {
    $gmaps = new GMaps();
    $config = array();
    $config['center'] = '14.5581629, 121.0241867';
    $config['zoom'] = '16';
    $config['map_width'] = '900px';
    $config['scrollwheel'] = false;

    //marker
    $marker = array();
    $marker['position'] = '14.5581629, 121.0241867';
    $marker['animation'] = 'DROP';
    $marker['title'] = 'test 1';
    $marker['infowindow_content'] = '<p> <b>Building Name </b> : 51639 <br><br> <b>Parking Slot</b> : p180, <br><br> <b>Price Per Hour</b> : 136 </p>';

    $gmaps->initialize($config);
    $gmaps->add_marker($marker);

    $marker = array();

    $marker['position'] = '14.5577893, 121.0251628';
    $marker['animation'] = 'DROP';
    $marker['title'] = 'test 2';
    $marker['infowindow_content'] = '<p> <b>Building Name </b> : 71565 <br><br> <b>Parking Slot</b> : p190 <br><br> Price Per Hour : 60 </p>';
    $gmaps->add_marker($marker);

    $map = $gmaps->create_map();

    return view('welcome')->with('map', $map);

});

//none token based routes via web
Route::get('user/verify', 'APIController@userVerify');
Route::get('eula', 'AccountSecurityController@generateEula');

/**
 * none token based routing
 */
Route::group(['middleware' => ['api'], 'prefix' => Copywrite::API_PREFIX], function() {
    Route::post('user/security_question', 'UserController@storeSecurityQuestions');
    Route::get('user/{id}/security_question', 'UserController@getSecurityQuestions');
    Route::post('user/{id}/security_question', 'UserController@verifySecurityQuestions');
    Route::post('user/account_recovery', 'UserController@accountRecovery');
    Route::resource('user_security', 'AccountSecurityController', ['only' => ['index', 'create', 'show']]);
});

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
        Route::resource('parkspace.pricing', 'ParkingSpacePricingController', ['only' => ['store', 'update', 'show', 'index']]);
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

/**
 * Booking Service
 */
Route::group(['middleware' => ['api'], 'prefix' => Copywrite::API_PREFIX], function() {
    Route::group(['middleware' => 'jwt-verify'], function() {
        Route::get('parkingspace.nearby', 'ParkingSpaceController@getNearbyParkingSpace');
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
