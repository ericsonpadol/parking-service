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
Route::get('/app.info', function () {
    $cow = Cowsayphp\Farm::create(\Cowsayphp\Farm\Cow::class);
    echo '<pre>';
    echo '<b>PARKING SERVICE: Health Check</b>';
    foreach ($_SERVER as $key => $spiels) {
        $cowResponse = $cow->say($key . ' => ' . $spiels);
        echo $cowResponse;
    }
    echo '</pre>';
});

Route::get('testpush', function () {
    event(new App\Events\testpush());
    return "Event has been sent!";
});

Route::get('/fire', function () {
    event(new App\Events\AppNotify);
    return 'fired';
});

Route::get('test', function() {
    return view('testpush');
});

Route::get('presence-test', function() {
    return view('presence_channel');
});

Route::get('/test-map', 'ParkingSpaceController@testMap');
Route::get('/test-distance-mapping', 'ParkingSpaceController@testDistanceMapping');

//none token based routes via web
Route::get('user/verify', 'APIController@userVerify');
Route::get('eula', 'AccountSecurityController@generateEula');
Route::get('user/approval', 'AdminController@approvedAccount'); //email approval only

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
        Route::resource('parking_space', 'ParkingSpaceController', ['only' => ['index']]);
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
        Route::resource('user.document', 'UserDocumentController', ['except' => 'create', 'edit']);
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
        Route::post('parkingspace.find', 'ParkingSpaceController@findParkingSpace');
        Route::get('parkingspace.select/{parkspace}', 'ParkingSpaceController@getSelectedParkingSpace');

        //messaging
        Route::post('messages/blast-message', 'UserMessageController@sendBlastMessage');
        Route::get('user/{fromUserId}/recipient/{toUserId}/messages', 'UserMessageController@getAllMessages');
        Route::post('user/{userId}/send-message', 'UserMessageController@sendMessage');
        Route::get('user/{userId}/incoming-message', 'UserMessageController@fetchIncomingMessages');
        Route::get('user/{userId}/outgoing-message', 'UserMessageController@fetchOutgoingMessages');
        Route::put('messages/{messageId}/set-to-read', 'UserMessageController@setMessageStatusToRead');
        Route::get('user/{fromUserId}/threaded-messages', 'UserMessageController@getAllThreadedMessages');
    });
});

/**
 * Push Notification Channel
 */
Route::group(['middleware' => ['api'], 'prefix' => Copywrite::API_PREFIX], function() {
    Route::group(['middleware' => 'jwt-verify'], function() {
        Route::resource('channels', 'PushChannelController', ['except' => 'create', 'edit']);
        Route::resource('subscriber.pushchannel', 'UserPushchannelController', ['except' => 'create', 'edit']);
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
