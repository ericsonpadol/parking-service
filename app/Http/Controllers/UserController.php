<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\ParkingSpace;
use App\Vehicle;
use App\Copywrite;
use App\Http\Requests;
use App\MailHelper;
use Validator;
use Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\CustomQueryBuilder;

class UserController extends Controller
{
    //configuration
    private $_streamLogger;
    private $_sqlCustom;

    public function __construct() {
        $this->_sqlCustom = new CustomQueryBuilder();
        $this->_streamLogger = new Logger($this);
        $this->_streamLogger->pushHandler(new StreamHandler('php://stderr', LOGGER::INFO));
    }

    /**
     * recover user account
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function accountRecovery(Request $request) {
        //validate request
        $validator = Validator::make($request->all(),
        [
            'mobile_number' => 'required|min:11|max:11'
        ], [
            'mobile_number.min' => Copywrite::INVALID_MOBILE_NUMBER,
            'mobile_number.max' => Copywrite::INVALID_MOBILE_NUMBER
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'http_code' => Copywrite::HTTP_CODE_422,
                'status_code' => Copywrite::STATUS_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_422);
        }

        $input = $request->only([
            'mobile_number'
        ]);

        $found = User::where(['mobile_number' => $input['mobile_number']])->first();

        if (!$found) {
            return response()->json([
                'message' => Copywrite::MOBILE_NUMBER_NOT_FOUND,
                'http_code' => Copywrite::HTTP_CODE_404,
                'status_code' => Copywrite::STATUS_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_404);
        }

        return response()->json([
            'user_id' => $found->id,
            'http_code' => Copywrite::HTTP_CODE_200,
            'status' => Copywrite::RESPONSE_STATUS_SUCCESS
        ], Copywrite::HTTP_CODE_200);
    }

    /**
     *  private function to get the user security information from the database return object
     *  this is used method verifySecurityQuestions
     * @param mixed $value
     * @return mixed
     */
    private function _getSecurityInformation($value) {
        $o = array();

        foreach($value as $v) {
            array_push($o, $v);
        }

        return $o;
    }

    /**
     * verify user security question
     * @param Request $request
     * @param String $id
     * @return \Illuminate\Http\Response
     */
    public function verifySecurityQuestions($id, Request $request) {
        $oUser = new User();
        $userInput = $request->only([
            'data'
        ]);

        $countData = count($userInput['data']);

        //service is always expecting 2-3 security questions if request contains > 3 security questions
        //return an error if data is less the < 2
        if ($countData > 3 || $countData < 2) {
            return response()->json([
                'message' => Copywrite::APP_CANNOT_PROCESS_SECQUESTIONS,
                'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                'http_code' => Copywrite::HTTP_CODE_400
            ], Copywrite::HTTP_CODE_400);
        }

        //loop thru the security questions if one security question return an error
        foreach($userInput['data'] as $key) {
            $params = [
                'user_id' => $id,
                'secques_id' => $key['secques_id'],
                'answer_value' => $key['answer_value']
            ];

            //validate security questions
            //checking null value
            if (empty($params['secques_id']) || empty($params['answer_value'])) {
                return response()->json([
                    'message' => empty($params['answer_value']) ?
                        Copywrite::ACCOUNT_SECURITY_ANSWER_NOT_FOUND : Copywrite::APP_CANNOT_PROCESS_SECQUESTIONS,
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                    'http_code' => Copywrite::HTTP_CODE_400
                ], Copywrite::HTTP_CODE_400);
            }

            $result = $oUser->verifySecurityQuestions($params);

            if ($result['status'] === 'failed') {
                return response()->json($result,  Copywrite::HTTP_CODE_400);
            }
        }

        function _getSecurityInformation($value) {
            return $value;
        }

        $keys = array_map(array($this, '_getSecurityInformation'), $result['data']);

        $securityinfo = current($keys);

        //create reset token
        $resetToken = str_random(6); //generated random token

        //update password from generated token
        $params = [
            'password' => password_hash($resetToken, PASSWORD_DEFAULT),
        ];

        $columns = [
            'id' => $id
        ];

        $result = $oUser->resetPassword($params, $columns);

        //create a reset password log
        $resetPasswordLogs = array();
        $resetTable = 'reset_password';
        $customColumns = ['email', 'reset_token'];
        foreach($securityinfo as $key=>$resetValues) {
           if ($key === 1) {
            $resetPasswordLogs = [
                $resetValues,
                $resetToken
            ];
           }


        }
        $resetLog = $this->_sqlCustom->resetPasswordQuery($resetPasswordLogs, $resetTable, $customColumns);

        if ($result === 'failed') {
            return response()->json([
                'message' => Copywrite::PASSWORD_UPDATE_FAIL,
                'http_status' => Copywrite::HTTP_CODE_500
            ], Copywrite::HTTP_CODE_500);
        }

        //mailbox parameters
        //@EBP : it's ok to index the array values here since the $mailParams is always expecting a value.
        $mailParams = [
            'mail_to_name' => $securityinfo[2],
            'mail_to_email' => $securityinfo[1],
            'reset_token' => $resetToken
        ];

        $emailHelper = new MailHelper();
        $mailbox = $emailHelper->accountRecovery($mailParams);

        return response()->json([
            'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
            'status_code' => Copywrite::STATUS_CODE_104,
            'http_code' => Copywrite::HTTP_CODE_200,
            'mail_result' => $mailbox,
            'reset_log' => $resetLog
        ], Copywrite::HTTP_CODE_200);
    }

    /**
     * get security question
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function getSecurityQuestions($id) {
        $oUser = new User();

        $params = ['user_id' => $id];

        $result = $oUser->getSecurityQuestions($params);

        if ($result['status'] === 'failed') {
            return response()->json($result, Copywrite::HTTP_CODE_500);
        }

        return response()->json($result, Copywrite::STATUS_CODE_200);
    }

    /**
     * store security question
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function storeSecurityQuestions(Request $request) {
        $validator = Validator::make($request->all(), [
            'data.*.secques_id' => 'required',
            'data.*.user_id' => 'required',
            'data.*.answer_value' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'http_code' => Copywrite::HTTP_CODE_422,
                'status_code' => Copywrite::STATUS_CODE_101,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_422);
        }

        $data = $request->only([
            'data'
        ]);

        $countData = count($data['data']);

        if ($countData > 4 || $countData < 2) {
            return response()->json([
                'message' => Copywrite::APP_CANNOT_PROCESS_SECQUESTIONS,
                'status' => Copywrite::RESPONSE_STATUS_FAILED,
                'http_code' => Copywrite::HTTP_CODE_400
            ], Copywrite::HTTP_CODE_400);
        }

        $now = date("Y-m-d H:i:s");

        $oUser = new User();
        $columns = ['secques_id', 'user_id', 'answer_value', 'created_at', 'updated_at'];
        $keys = array_map(create_function('$o', 'return $o;'), $data['data']);

       foreach($keys as $index => $value) {
            $params = [
                $value['secques_id'],
                $value['user_id'],
                md5($value['answer_value']),
                $now,
                $now,
            ];

            $result = $oUser->setAnswerSecurityQuestions($params, $columns);

            if ($result['status'] === 'failed') {
                return response()->json($result, Copywrite::HTTP_CODE_500);
            }
       }

        return response()->json($result, Copywrite::HTTP_CODE_200);
    }

    /**
     * update user password
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'password' => 'required|min:8|regex:/(\d+)/u|regex:/([a-z]+)/u|regex:/([A-Z]+)/u|regex:/(\W+)/u',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'http_code' => Copywrite::HTTP_CODE_422,
                'status_code' => Copywrite::STATUS_CODE_101,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_422);
        }

        $oUser = new User();
        $params = ['password' => password_hash($request['password'], PASSWORD_DEFAULT)];
        $columns = ['id' => $request['user_id']];

        $result = $oUser->resetPassword($params, $columns);

        if ($result === 'failed') {
            return response()->json([
                'message' => Copywrite::PASSWORD_UPDATE_FAIL,
                'status' => Copywrite::HTTP_CODE_406,
            ], Copywrite::HTTP_CODE_406);
        }

        return response()->json([
            'message' => Copywrite::PASSWORD_UPDATE_SUCCESS,
            'status' => Copywrite::HTTP_CODE_200,
        ], Copywrite::HTTP_CODE_200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $users = User::all();

        return response()->json(['data' => $users], Copywrite::HTTP_CODE_200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        /**
         * Note:
         * That new user registration are found on the APIController
         */
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $userId) {
        $userAccount = User::find($userId);

        if (!$userAccount) {
            return response()->json([
                        'message' => Copywrite::USER_NOT_FOUND,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'http_code' => Copywrite::HTTP_CODE_404
                            ], Copywrite::HTTP_CODE_404);
        }

        $values = $request->except(['password']);

        $validator = Validator::make($values, [
                    'email' => 'email|max:255|unique:users,email|filled',
                    'mobile_number' => 'min:11|max:11|unique:users,mobile_number|filled|numeric',
                    'full_name' => 'string|max:255|filled',
                    'image_uri' => 'url|filled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'http_code' => Copywrite::HTTP_CODE_422,
                'status_code' => Copywrite::STATUS_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ], Copywrite::HTTP_CODE_422);
        }

        $userAccount->update($values);

        return response()->json([
                    'messages' => Copywrite::DEFAULT_UPDATE_SUCCESS . ' ' . $request->get('id'),
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                    'http_code' => Copywrite::HTTP_CODE_200], Copywrite::HTTP_CODE_200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $userAccount = User::find($id);

        if (!$userAccount) {
            return response()->json([
                        'message' => Copywrite::USER_NOT_FOUND,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'http_code' => Copywrite::HTTP_CODE_404
                            ], Copywrite::HTTP_CODE_404);
        }

        //validate user has no transactions
        $parkingspaces = $userAccount->parkingspaces;
        $vehicles = $userAccount->vehicles;

        if (sizeof($parkingspaces) > 0 && sizeof($vehicles) > 0) {
            return response()->json([
                        'message' => str_replace(':useraccount:', $userAccount->email, Copywrite::USER_DELETE_RESTRICT),
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'http_code' => Copywrite::HTTP_CODE_409
                            ], Copywrite::HTTP_CODE_409);
        }

        $userAccount->delete();

        return response()->json([
                    'message' => Copywrite::USER_DELETE_ALLOWED,
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                    'http_code' => Copywrite::HTTP_CODE_200
                        ], Copywrite::HTTP_CODE_200);
    }

}
