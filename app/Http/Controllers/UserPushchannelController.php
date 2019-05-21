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
use Carbon\Carbon;
use Faker\Provider\cs_CZ\DateTime;

class UserPushchannelController extends Controller
{
    //configuration
    private $_logger = '';
    private $_sqlCustom;
    private $_channelType = array('public', 'private', 'presence');

    protected $dateNow = '';

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
    public function store(Request $request, $userid)
    {
        $user = User::find($userid);

        if (!$user) {
            return response()->json([
                'messages' => Copywrite::USER_NOT_FOUND,
                'status' => Copywrite::RESPONSE_STATUS_FAILED,
                'http_code' => Copywrite::HTTP_CODE_404
            ], Copywrite::HTTP_CODE_404);
        }

        //create channel parameters
        $pushChannel = new PushChannel();
        $this->dateNow = Carbon::today();
        $channelParameters = [
            'channel_id' => $request->channel_id,
            'user_id' => $userid,
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ];

        //check if there is already a subscription available

        $result = $pushChannel->createSubChannelRelationship($channelParameters);

        if (!$result) {
            return response()->json([
                'message' => Copywrite::SERVER_ERROR,
                'http_code' => Copywrite::HTTP_CODE_500,
                'status_code' => Copywrite::STATUS_CODE_500,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_500)->header(Copywrite::HEADER_CONVID, Session::getId());
        }

        return response()->json($result, $result['http_code'])->header(Copywrite::HEADER_CONVID, Session::getId());
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
