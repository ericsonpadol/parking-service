<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Copywrite extends Model
{

    /**
     * DEFAULT COPYWRITES
     */
    const RESPONSE_STATUS_FAILED = 'failed';
    const SECURITY_QUESTION_SUCCESS = 'security questions saved';
    const RESPONSE_STATUS_SUCCESS = 'success';
    const USER_CREATED_SUCCESS = 'new user created successfully';
    const USER_CREATED_FAILED = 'cannot create new user';
    const USER_NOT_ACTIVATED = 'user is not activated';
    const DEFAULT_UPDATE_SUCCESS = 'updated successfully';
    const DEFAULT_UPDATE_FAILED = 'cannot update';
    const PARKING_SPACE_CREATE_SUCCESS = 'new parking space added successfully';
    const PARKING_SPACE_CREATE_FAILED = 'cannot create new parking space';
    const USER_DELETE_ALLOWED = ':useraccount: successfully deleted';
    const PARKING_SPACE_DELETE_ALLOWED = 'parking space deleted successfully';
    const VEHICLE_CREATE_SUCCESS = 'new vehicle successfully created';
    const VEHICLE_DELETE_ALLOWED = 'vehicle deleted successfully';
    const PASSWORD_UPDATE_SUCCESS = 'password changed successfully';
    const ACTIVATION_STATUS_SUCCESS = 'user account activated';
    const UNLOCK_STATUS_SUCCESS = 'congratulations, you\'re account is unlocked.';

    /**
     * LOGGING
     */
    const LOG_RESET_TOKEN_SUCCESS = 'token generated successfully';
    const LOG_RESET_TOKEN_FAIL = 'cannot generate reset token';
    const LOGGER_INFO = 'logged info';
    const LOGGER_WARN = 'logged warn';
    const LOGGER_ERROR = 'logged error';


    /**
     * ERROR COPYWRITES
     */
    const SERVER_DOWNTIME = 'server is down';
    const PASSWORD_FORMAT_ERROR = 'Password requires one uppercase, one numeric, one special character';
    const USER_NOT_FOUND = 'user not found';
    const PARKING_SPACE_NOT_FOUND = 'parking space not found';
    const INVALID_CREDENTIALS = 'invalid email and password, you have only 3 tries before your account locks out';
    const USER_DELETE_RESTRICT = ':useraccount: has transactions and cannot be deleted';
    const PARKING_SPACE_DELETE_RESTRICT = ':parkingspace: has transactions and cannot be deleted';
    const PARKING_SPACE_INVALID = 'invalid parking spaces';
    const VEHICLE_INVALID = 'invalid vehicle';
    const VEHICLE_NOT_FOUND = 'vehicle not found';
    const VEHICLE_DELETE_RESTRICT = ':vehicle: has transactions and cannot be deleted';
    const PASSWORD_UPDATE_FAIL = 'fail to update your password';
    const ACTIVATION_CODE_FAIL = 'broken activation code';
    const ACTIVATION_STATUS_FAIL = 'fail to activate user account';
    const ACCOUNT_ACTIVATION_ERROR = 'please check your email and activate your account.';
    const ACCOUNT_ERROR = 'account was locked out due to multiple failed logon ' .
        'attempts that occurred in a short period of time. This may occur if an unauthorized user attempts.' .
        'Please wait for 30 mins to login again.';



    /**
     * HTTP CODES
     */
    const HTTP_CODE_404 = 404; //not found code
    const HTTP_CODE_401 = 401; //unauthorized
    const HTTP_CODE_400 = 400; //bad request
    const HTTP_CODE_409 = 409; //conflict
    const HTTP_CODE_406 = 406; //Not Acceptable
    const HTTP_CODE_422 = 422; //Unprocessable Entity
    const HTTP_CODE_200 = 200; //success code
    const HTTP_CODE_201 = 201;
    const HTTP_CODE_500 = 500; //internal server error

    /**
     * STATUS CODES
     */
    const STATUS_CODE_101 = 101; //unsuccessful login
    const STATUS_CODE_100 = 100; //successful login
    const STATUS_CODE_102 = 102; //inactive user account
    const STATUS_CODE_103 = 103; //reset account
    const STATUS_CODE_104 = 104; //account is using temp password
    const STATUS_CODE_105 = 105; //account is activated
    const STATUS_CODE_106 = 106; //account is locked
    const STATUS_CODE_500 = 500; //internal server error
    const STATUS_CODE_108 = 108; //unlock account
    const STATUS_CODE_200 = 200; //OK

    /**
     * AUTHENTICATION BLOCK
     */
    const AUTH_USER_NOT_FOUND = 'user_not_found';
    const AUTH_TOKEN_EXPIRED = 'token_expired';
    const AUTH_TOKEN_INVALID = 'token_invalid';
    const AUTH_TOKEN_ABSENT = 'token_absent';

    /**
     * Mail Copywrite;
     *
     */
    const MAIL_RESET_PASSWORD_SUBJECT = 'LGPARK IT: RESET PASSWORD NOTIFICATION';
    const MAIL_RESET_PASSWORD_BODY_HTML = 'Hi <b>:full_name:</b>, <p>You have requested a password reset,'
        . ' please use this reset token as your temporary password <b><span class="tokenize">:reset_token:</span></b>.</p>'
        . ' <p class="mail_important">Please ignore this email if you did not request a password change. '
        . ' <br> <b>Do not reply to this email</b>.</p>';

    const MAIL_ACTIVATION_SUBJECT = 'LGPARK IT: ACTIVATE YOUR ACCOUNT';
    const MAIL_ACTIVATION_BODY_HTML = 'You\'re nearly there! <b>:full_name:</b>. <br>'
        . 'We just need to verify your email address to complete your registration. <br><br>'
        . '<a href=":activation_link:">:activation_spiel:</a> <br><br>'
        . 'Please click the link to activate your account. <br>'
        . 'If you have not registered to PARK IT, please ignore this email.';

    /**
     * Custom Spiel
     */

    const MAIL_ACTIVATION_SPIEL = 'Click to activate your account.';
    const MAIL_ACTIVATED_TITLE_FAIL = 'Ooppss! Something just broke.';
    const MAIL_ACTIVATED_TITLE_SUCCESS = 'Welcome <b>:full_name:</b> to PARK-IT!';
    const MAIL_ACTIVATED_BODY = 'We\'re so happy you have joined us. You\'re account is now activated.';

    /**
     * remember to put this on the app\config
     */
    const API_PREFIX = 'parking-api';

}
