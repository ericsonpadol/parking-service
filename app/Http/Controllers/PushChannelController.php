<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
use App\Http\Requests;
use Session;
use Illuminate\Validation\Rule;

class PushChannelController extends Controller
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
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = User::find($request->created_by);

        if (!$user) {
            return response()->json([
                'messages' => Copywrite::USER_NOT_FOUND,
                'status' => Copywrite::RESPONSE_STATUS_FAILED,
                'http_code' => Copywrite::HTTP_CODE_404
            ], Copywrite::HTTP_CODE_404);
        }

        $channelInput = [
            'channels' => $this->_channelType,
            'channel' => $request->channel_type
        ];

        //channel validator
        $channelValidator = Validator::make($channelInput, [
            'channel' => 'required|in_array:channels.*'
        ]);

        if ($channelValidator->fails()) {
            return response()->json([
                'message' => $channelValidator->errors(),
                'http_code' => Copywrite::HTTP_CODE_422,
                'status_code' => Copywrite::STATUS_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_422)
                ->header(Copywrite::HEADER_CONVID, Session::getId());
        }

        //validate request
        $validator = Validator::make($request->all(), [
            'channel_name' => 'required|string|unique:push_channels',
            'created_by' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'http_code' => Copywrite::HTTP_CODE_422,
                'status_code' => Copywrite::STATUS_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_422)
                ->header(Copywrite::HEADER_CONVID, Session::getId());
        }

        //prepare data
        $channelParams = [
            'channel_name' => $request->channel_name,
            'channel_type' => $request->channel_type,
            'created_by' => $request->created_by,
            'ch_desc' => $request->ch_desc,
        ];

        try {
            $createPushChannelResult = PushChannel::create($channelParams);

            //log
            Log::info(CustomLogger::getCurrentRoute() .
                CustomLogger::getConversationId() .
                CustomLogger::DB_CALL . serialize(DB::getQueryLog()));

            $this->_logger->addInfo(CustomLogger::getCurrentRoute() .
                CustomLogger::getConversationId() .
                CustomLogger::DB_CALL . serialize(DB::getQueryLog()));

            return response()->json([
                'message' => Copywrite::CHANNEL_CREATE_SUCCESS,
                'http_code' => Copywrite::HTTP_CODE_200,
                'status' => Copywrite::RESPONSE_STATUS_SUCCESS
            ], Copywrite::HTTP_CODE_200);

        } catch (Exception $e) {
            return [
                'message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'stack_trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'http_code' => Copywrite::HTTP_CODE_500
            ];
        }
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
