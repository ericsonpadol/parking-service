<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Message as UserMessage;
use App\Copywrite;
use App\User;

use App\Http\Requests;
use Validator;
use Session;
use DB;
use Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\CustomLogger;

class UserMessageController extends Controller
{
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
    public function store(Request $request)
    {
        //
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

    /**
     * create draft message
     * @param request
     * @param int $userId
     * @return mixed
     */
    public function sendMessage(Request $request, $userId)
    {
        $oMessage = new UserMessage();

        //check if user is available
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'messages' => Copywrite::USER_NOT_FOUND,
                'status' => Copywrite::RESPONSE_STATUS_FAILED,
                'http_code' => Copywrite::HTTP_CODE_404
            ], Copywrite::HTTP_CODE_404)->header(Copywrite::HEADER_CONVID, Session::getId());
        }

        //validate input file
        $validator = Validator::make($request->all(), [
            'to_user_id' => 'required|integer',
            'message' => 'required'
        ]);

        if ($validator->fails()) {
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'http_code' => Copywrite::HTTP_CODE_422,
                    'status_code' => Copywrite::STATUS_CODE_404,
                    'status' => Copywrite::RESPONSE_STATUS_FAILED
                ], Copywrite::HTTP_CODE_422)
                    ->header(Copywrite::HEADER_CONVID, Session::getId());
            }
        }

        //prepare data
        $messageInput = [
            'message' => $request->message,
            'to_user_id' => $request->to_user_id,
            'from_user_id' => $userId
        ];

        $result = $oMessage->sendMessage($messageInput);
        $httpCode = $result['status'] === 'success' ? Copywrite::HTTP_CODE_200 : Copywrite::HTTP_CODE_500;

        return response()->json($result, $httpCode)->header(Copywrite::HEADER_CONVID, Session::getId());
    }

    /**
     * get user incoming messages
     * @param int $userId
     * @return mixed
     */
    public function fetchIncomingMessages($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'messages' => Copywrite::USER_NOT_FOUND,
                'status' => Copywrite::RESPONSE_STATUS_FAILED,
                'http_code' => Copywrite::HTTP_CODE_404
            ], Copywrite::HTTP_CODE_404)->header(Copywrite::HEADER_CONVID, Session::getId());
        }

        $incomingMessage = UserMessage::where('to_user_id', $userId)->get();

        return response()->json([
            'data' => $incomingMessage,
            'http_code' => Copywrite::HTTP_CODE_200,
            'status' => Copywrite::RESPONSE_STATUS_SUCCESS
        ], Copywrite::HTTP_CODE_200)->header(Copywrite::HEADER_CONVID, Session::getId());
    }

    public function fetchOutgoingMessages($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'messages' => Copywrite::USER_NOT_FOUND,
                'status' => Copywrite::RESPONSE_STATUS_FAILED,
                'http_code' => Copywrite::HTTP_CODE_404
            ], Copywrite::HTTP_CODE_404)->header(Copywrite::HEADER_CONVID, Session::getId());
        }

        $outgoingMessage = UserMessage::where('from_user_id', $userId)->get();

        return response()->json([
            'data' => $outgoingMessage,
            'http_code' => Copywrite::HTTP_CODE_200,
            'status' => Copywrite::RESPONSE_STATUS_SUCCESS
        ], Copywrite::HTTP_CODE_200)->header(Copywrite::HEADER_CONVID, Session::getId());
    }
}
