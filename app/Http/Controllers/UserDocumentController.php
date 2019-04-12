<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\UserDocument;
use App\CustomLogger;
use App\Copywrite;
use Validator;
use App\MailHelper;
use Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use DB;
use Session;


use App\Http\Requests;

class UserDocumentController extends Controller
{
    private $_logger = '';

    public function __construct()
    {
        DB::connection()->enableQueryLog();
        $this->_logger = new Logger('vehicles-update');
        $this->_logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($user)
    {
        //show all uploaded documents by the user
        $userDocuments = UserDocument::where('user_id', $user)->get();

        //log
        Log::info(CustomLogger::getCurrentRoute() .
            CustomLogger::getConversationId() .
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));

        Log::info(CustomLogger::getCurrentRoute() .
            CustomLogger::getConversationId() .
            CustomLogger::RESULT . serialize($userDocuments));

        $this->_logger->addInfo(CustomLogger::getCurrentRoute() .
            CustomLogger::getConversationId() .
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));

        $this->_logger->addInfo(CustomLogger::getCurrentRoute() .
            CustomLogger::getConversationId() .
            CustomLogger::RESULT . serialize($userDocuments));

        return response()->json([
            'data' => $userDocuments ? $userDocuments : [],
            'http_code' => Copywrite::HTTP_CODE_200,
            'status_code' => Copywrite::STATUS_CODE_200,
            'status' => Copywrite::RESPONSE_STATUS_SUCCESS
        ], Copywrite::HTTP_CODE_200);
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
    public function store(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => Copywrite::USER_NOT_FOUND,
                'http_code' => Copywrite::HTTP_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED,
                'status_code' => Copywrite::STATUS_CODE_404
            ], Copywrite::HTTP_CODE_404)->header(Copywrite::HEADER_CONVID, Session::getId());
        }

        $validator = Validator::make($request->all(), [
            'docu_type' => 'required|string|max:60',
            'docu_title' => 'required|string|max:250',
            'docu_uri' => 'required|url'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'http_code' => Copywrite::HTTP_CODE_422,
                'status_code' => Copywrite::STATUS_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_422)->header(Copywrite::HEADER_CONVID, Session::getId());
        }

        $values = $request->all();

        if ($user->userdocuments()->create($values)) {
            //fire an email if document is successfully added
            $emailHelper = new MailHelper();
            $mailParams = [
                'user_email' => $user->email,
                'user_fullname' => $user->full_name,
                'docu_title' => $request->docu_title,
                'docu_uri' => $request->docu_uri,
                'docu_message'=> $request->user_message
            ];

            $fireMailbox = $emailHelper->reviewUserDocumentMail($mailParams);

            return response()->json([
                'message' => Copywrite::USER_DOCUMENT_ADD_SUCCESS,
                'http_code' => Copywrite::HTTP_CODE_200,
                'status_code' => Copywrite::STATUS_CODE_200,
                'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                'mail_result' => $fireMailbox,
            ], Copywrite::HTTP_CODE_200)->header(Copywrite::HEADER_CONVID, Session::getId());
        } else {
            return response()->json([
                'message' => Copywrite::SERVER_ERROR,
                'http_code' => Copywrite::HTTP_CODE_500,
                'status_code' => Copywrite::STATUS_CODE_500,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_500)->header(Copywrite::HEADER_CONVID, Session::getId());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($userid, $documentid)
    {
        $user = User::find($userid);

        if (!$user) {
            return response()->json([
                'message' => Copywrite::USER_NOT_FOUND,
                'http_code' => Copywrite::HTTP_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_404)->header(Copywrite::HEADER_CONVID, Session::getId());
        }

        $userDocuments = $user->userdocuments->find($documentid);

        //log
        Log::info(CustomLogger::getCurrentRoute() .
            CustomLogger::getConversationId() .
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));

        Log::info(CustomLogger::getCurrentRoute() .
            CustomLogger::getConversationId() .
            CustomLogger::RESULT . serialize($userDocuments));

        $this->_logger->addInfo(CustomLogger::getCurrentRoute() .
            CustomLogger::getConversationId() .
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));

        $this->_logger->addInfo(CustomLogger::getCurrentRoute() .
            CustomLogger::getConversationId() .
            CustomLogger::RESULT . serialize($userDocuments));

        return response()->json([
            'data' => $userDocuments ? $userDocuments : [],
            'http_code' => Copywrite::HTTP_CODE_200,
            'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
        ], Copywrite::HTTP_CODE_200)->header(Copywrite::HEADER_CONVID, Session::getId());
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
    public function destroy($userid, $documentid)
    {
        $user = User::find($userid);

        if (!$user) {
            return response()->json([
                'message' => Copywrite::USER_NOT_FOUND,
                'http_code' => Copywrite::HTTP_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_404)->header(Copywrite::HEADER_CONVID, Session::getId());
        }

        $userDocuments = $user->userdocuments->find($documentid);

        if (!$userDocuments) {
            return response()->json([
                'message' => Copywrite::USER_DOCUMENT_NOT_FOUND,
                'http_code' => Copywrite::HTTP_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_404)->header(Copywrite::HEADER_CONVID, Session::getId());
        }

        if ($userDocuments->delete()) {
            return response()->json([
                'message' => Copywrite::USER_DOCUMENT_DELETE_SUCCESS,
                'http_code' => Copywrite::HTTP_CODE_200,
                'status' => Copywrite::RESPONSE_STATUS_SUCCESS
            ], Copywrite::HTTP_CODE_200)->header(Copywrite::HEADER_CONVID, Session::getId());
        } else {
            return response()->json([
                'message' => Copywrite::SERVER_ERROR,
                'http_code' => Copywrite::HTTP_CODE_500,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_500)->header(Copywrite::HEADER_CONVID, Session::getId());
        }
    }
}
