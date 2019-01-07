<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\User;
use App\Copywrite;
use App\MailHelper;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\RegisterNewUserRequest;
use App\Http\Requests\LoginAuthenticateRequest;
use App\Http\Requests\RequestResetPassword;
use SendGrid;

//use mediaburst\ClockworkSMS\Clockwork as SMSGenerator;
//use mediaburst\ClockworkSMS\ClockworkException as SMSGeneratorException;



class APIController extends Controller
{

    public function resetPassword(RequestResetPassword $request) {
        $userInput = $request->only(['email']);

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

        $sendGrid = new SendGrid(env('SENDGRID_KEY'));

        $fireMail = $sendGrid->send($mailbox);

        return response()->json([
                    'messages' => Copywrite::DEFAULT_UPDATE_SUCCESS . ' ' . $verifiedUser->email,
                    'email_status_code' => $fireMail->statusCode(),
                    'email_header' => $fireMail->headers(),
                    'email_body' => $fireMail->body(),
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                    'http_code' => Copywrite::HTTP_CODE_200], Copywrite::HTTP_CODE_200);
    }

    public function register(RegisterNewUserRequest $request) {
        $userInput = $request->only([
            'email',
            'full_name',
            'password',
            'mobile_number',
        ]);

        $userInput['password'] = password_hash($userInput['password'], PASSWORD_DEFAULT);

        User::create($userInput);
        $user = User::first();
        $token = JWTAuth::fromUser($user);
        return response()->json([
                    'message' => Copywrite::USER_CREATED_SUCCESS,
                    'token' => $token,
                    'http_code' => Copywrite::HTTP_CODE_200,
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS
                        ], Copywrite::HTTP_CODE_200);
    }

    public function login(LoginAuthenticateRequest $request) {
        $userInput = $request->only([
            'email',
            'password'
        ]);

        if (!$token = JWTAuth::attempt($userInput)) {
            return response()->json([
                        'message' => Copywrite::INVALID_CREDENTIALS,
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'http_code' => Copywrite::HTTP_CODE_401,
                            ], Copywrite::HTTP_CODE_401);
        }

        return response()->json([
                    'token' => $token,
                    'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
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

}
