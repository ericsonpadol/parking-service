<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Copywrite;
use Session;
use App\MailHelper;
use App\Http\Requests;
use App\CustomLogger;
use DB;
use Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class AdminController extends Controller
{

    public function __construct()
    {
        DB::connection()->enableQueryLog();
        $this->_logger = new Logger('AdminController');
        $this->_logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
    }

    /**
     * Approved user account
     * @param Illuminate\Http\Request
     * @return view
     */
    public function approvedAccount(Request $request)
    {
        $user = User::where('email', $request->useremail)->first();

        if (!$user) {
            return response()->json([
                'message' => Copywrite::USER_NOT_FOUND,
                'http_code' => Copywrite::HTTP_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_404)->header(Copywrite::HEADER_CONVID, Session::getId());
        }

        $user->is_approved = 'true';

        try {
            $emailHelper = new MailHelper();
            $mailParams = [
                'mail_to_email' => $user->email,
                'user_fullname' => $user->full_name
            ];

            if ($user->update()) {
                //application log
                Log::info(CustomLogger::getConversationId() .
                CustomLogger::getCurrentRoute() .
                CustomLogger::DB_CALL . serialize(DB::getQueryLog()));

                //stream logging
                $this->_logger->addInfo(CustomLogger::getConversationId() .
                CustomLogger::getCurrentRoute() .
                CustomLogger::DB_CALL . serialize(DB::getQueryLog()));

                $fireApprovedMailbox = $emailHelper->approvedNotifyUserMail($mailParams);

                return response()->json([
                    'message' => Copywrite::ACCOUNT_APPROVED,
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                    'http_code' => Copywrite::HTTP_CODE_200,
                    'status_code' => Copywrite::STATUS_CODE_200,
                    'mailbox_result' => $fireApprovedMailbox,
                ], Copywrite::HTTP_CODE_200)->header(Copywrite::HEADER_CONVID, Session::getId());

            } else {
                return response()->json([
                    'message' => Copywrite::SERVER_ERROR,
                    'status' => Copywrite::RESPONSE_STATUS_FAILED,
                    'http_code' => Copywrite::HTTP_CODE_500,
                    'status_code' => Copywrite::STATUS_CODE_500,
                ], Copywrite::HTTP_CODE_500)->header(Copywrite::HEADER_CONVID, Session::getId());
            }

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
}
