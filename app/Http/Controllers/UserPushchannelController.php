<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\User;
use App\PushChannel;
use App\Copywrite;
use App\CustomQueryBuilder;
use Validator;
use Log;
use DB;
use App\CustomLogger;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Session;

class UserPushchannelController extends Controller
{
    //configuration
    private $_logger = '';
    private $_sqlCustom;
    private $_channelType = array('public', 'private', 'presence');

    public function __construct()
    {
        $this->_sqlCustom = new CustomQueryBuilder();
        $this->_logger = new Logger($this);
        $this->_logger->pushHandler(new StreamHandler('php://stderr', LOGGER::INFO));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'messages' => Copywrite::USER_NOT_FOUND,
                'status' => Copywrite::RESPONSE_STATUS_FAILED,
                'http_code' => Copywrite::HTTP_CODE_404
            ], Copywrite::HTTP_CODE_404);
        }

        //create channel parameters

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
