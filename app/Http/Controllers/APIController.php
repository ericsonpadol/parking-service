<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\User;
use App\Copywrite;
use App\MailHelper;
use App\CustomQueryBuilder;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\RegisterNewUserRequest;
use App\Http\Requests\LoginAuthenticateRequest;
use App\Http\Requests\RequestResetPassword;
use App\ParkingAuditLog;

//use mediaburst\ClockworkSMS\Clockwork as SMSGenerator;
//use mediaburst\ClockworkSMS\ClockworkException as SMSGeneratorException;



class APIController extends Controller
{

    public function resetPassword(RequestResetPassword $request) {
        $userInput = $request->only(['email']);
        $sqlCustom = new CustomQueryBuilder;
        $resetTable = 'reset_password';
        $customColumns = ['email', 'reset_token'];

        $verifiedUser = User::where('email', $userInput)->first();

        if (!$verifiedUser) {
            return response()->json([
                        'message' => Copywrite::USER_NOT_FOUND,
                        'http_code' => Copywrite::HTTP_CODE_404,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED
                            ], Copywrite::HTTP_CODE_404);
        }

        //update user account
        $resetToken = str_random(6); //generate random token password

        $verifiedUser->password = password_hash($resetToken, PASSWORD_DEFAULT);

        $params = [$verifiedUser->email, $resetToken];

        $resetLog = $sqlCustom->resetPasswordQuery($params, $resetTable, $customColumns);

        $verifiedUser->update();

        /**
         * send password via mobile text
         */
        //clockwork sms object
//        $sms = new SMSGenerator(env('SMS_KEY'));
//
//        //params
//        $smsParams = [
//            'to' => '639472421651',
//            'message' => 'This is your reset token ' . $resetToken
//        ];
//
//        $result = $sms->send($smsParams);
//
//        var_dump($result);
//
//        if ($result['success']) {
//            echo 'message fired: ' . $result['id'];
//        } else {
//            echo 'message failed: ' . $result['error_message'];
//        }

        $mailParams = [
            'mail_to_name' => $verifiedUser->full_name,
            'mail_to_email' => $verifiedUser->email,
            'reset_token' => $resetToken
        ];

        $emailHelper = new MailHelper();

        $mailbox = $emailHelper->createResetPasswordMail($mailParams);

        /**
         * if $mailbox resulted to 1 it means email is fired successfully.
         */
        return response()->json([
                    'message' => Copywrite::DEFAULT_UPDATE_SUCCESS . ' ' . $verifiedUser->email,
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                    'reset_log' => $resetLog,
                    'mail_result' => $mailbox,
                    'http_code' => Copywrite::HTTP_CODE_200], Copywrite::HTTP_CODE_200);
    }

    public function register(RegisterNewUserRequest $request) {
        $logger = new ParkingAuditLog();
        $userInput = $request->only([
            'email',
            'full_name',
            'password',
            'mobile_number',
        ]);

        //hashing options
        $options = [
            'cost' => '11',
        ];

        $activationSaltString = md5(openssl_random_pseudo_bytes(60));
        $activationString = md5(microtime().$userInput['email'].openssl_random_pseudo_bytes(60), FALSE) .
            $activationSaltString;

        $defaultUserValues = [
            'is_activated' => 'false',
            'is_lock' => 'false',
            'is_lock_count' => 0,
            'activation_token' => $activationString,
        ];

        $mergeUserVals = array_merge($userInput, $defaultUserValues);

        $mergeUserVals['password'] = password_hash($userInput['password'], PASSWORD_BCRYPT, $options);

        $cUser = User::create($mergeUserVals);
        //catch insert error if user is not successfully created
        //pending changes will needs to be done asap
        $user = User::first();
        $token = JWTAuth::fromUser($user);

        //fire an email that the user is not activated
        if ($cUser['is_activated'] == 'false') {
            $token = null;

            $mailParams = [
                'mail_fullname' => $request['full_name'],
                'activation_spiel' => Copywrite::MAIL_ACTIVATION_SPIEL,
                'activation_link' => url('user/verify') . '?active=' . $activationString,
                'mail_to_email' => $request['email'],
                'mail_to_name' => $request['full_name']
            ];

            $emailHelper = new MailHelper();
            $fireMailbox = $emailHelper->accountVerificationMail($mailParams);

        }

        $logParams = [
            'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
            'action' => 'register',
            'log' => $token ? Copywrite::USER_CREATED_SUCCESS : Copywrite::USER_NOT_ACTIVATED,
            'user_id' => $cUser['id']
        ];

        $conversationId = $logger->auditLogger($logParams);

        return response()->json([
                    'message' => $token ? Copywrite::USER_CREATED_SUCCESS : Copywrite::USER_NOT_ACTIVATED,
                    'conv_id' => $conversationId,
                    'token' => $token,
                    'http_code' => $token ? Copywrite::HTTP_CODE_200 : Copywrite::HTTP_CODE_401,
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS
            ], $token ? Copywrite::HTTP_CODE_200 : Copywrite::HTTP_CODE_401);
    }

    public function login(LoginAuthenticateRequest $request) {
        $queryBuilder = new CustomQueryBuilder();
        $userInput = $request->only([
            'email',
            'password'
        ]);

        if (!$token = JWTAuth::attempt($userInput)) {
            return response()->json([
                        'message' => Copywrite::INVALID_CREDENTIALS,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'status_code' => Copywrite::STATUS_CODE_101,
                        'http_code' => Copywrite::HTTP_CODE_401,
                            ], Copywrite::HTTP_CODE_401);
        }

        $queryTable = 'reset_password';
        //$params is a where clause since we are invoking a select method
        $params = ['where_clause' => 'email=' . '"' . $userInput['email'] . '" and '
            . 'reset_token=' . '"' . $userInput['password'] . '"'];

        $customColumns = ['email', 'activation'];

        //check reset password activation
        $resetFound = $queryBuilder->getResetPasswordDetails($params, $queryTable, $customColumns);

        //this always will return 1 row of array so hard coding array[0] is not a problem.
        if ($resetFound && $resetFound[0]['activation'] == 0) {
            $resetTokenParams = ['email' => $userInput['email']];
            $queryBuilder->activatePasswordToken($resetTokenParams);
        }

        if ($resetFound) {
            switch ($resetFound[0]['activation']) {
                case 0:
                    $resetAccount = Copywrite::STATUS_CODE_103;
                    break;

                case 1:
                    $resetAccount = Copywrite::STATUS_CODE_104;
                    break;

                default:
                    $resetAccount = Copywrite::STATUS_CODE_100;
            }
        } else {
            $resetAccount = Copywrite::STATUS_CODE_100;
        }
        //check wheter the password use is a reset token or not


        return response()->json([
                    'token' => $token,
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                    'status_code' => $resetAccount,
                    'http_code' => Copywrite::HTTP_CODE_200,
                        ], Copywrite::HTTP_CODE_200);
    }

    public function getAuthenticatedUser() {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                            'message' => Copywrite::AUTH_USER_NOT_FOUND,
                            'status' => Copywrite::RESPONSE_STATUS_FAILED
                                ], Copywrite::HTTP_CODE_404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                        'message' => Copywrite::AUTH_TOKEN_EXPIRED,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'trace' => $e->getStatusCode()
                            ]
                            , Copywrite::HTTP_CODE_401);
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                        'message' => Copywrite::AUTH_TOKEN_INVALID,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'trace' => $e->getStatusCode()
                            ]
                            , Copywrite::HTTP_CODE_401);
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                        'message' => Copywrite::AUTH_TOKEN_ABSENT,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'trace' => $e->getStatusCode()
                            ], Copywrite::HTTP_CODE_401);
        }

        return response()->json([
                    'data' => $user,
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                    'http_code' => Copywrite::HTTP_CODE_200,
                        ], Copywrite::HTTP_CODE_200);
    }

    /**
     * this function will activate the created user account
     * @param $request Request
     * @return json
     */
    public function userVerify(Request $request) {
        $uriRequest = $request->only([
            'active'
        ]);

        $found = User::verifyUserAccount($uriRequest);
        $result = $found['result'];

        if ($found['status'] === 'failed') {
            $params = [
                'message' => $found['message'],
                'title' => Copywrite::MAIL_ACTIVATED_TITLE_FAIL,
                'http_code' => Copywrite::HTTP_CODE_400,

            ];
        } else {
            //activate the account
            $toReplace = ['/:full_name:/'];
            $fromReplace  = [$result->full_name];
            $mailActivatedTitleSuccess = preg_replace($toReplace, $fromReplace, Copywrite::MAIL_ACTIVATED_TITLE_SUCCESS);

            $params = [
                'title' => $mailActivatedTitleSuccess,
                'message' => Copywrite::MAIL_ACTIVATED_BODY,
                'http_code' => Copywrite::HTTP_CODE_200
            ];
        }

        return view('account_verification', $params);
    }

}
