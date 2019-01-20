<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Copywrite extends Model
{

    /**
     * DEFAULT COPYWRITES
     */
    const RESPONSE_STATUS_FAILED = 'failed';
    const RESPONSE_STATUS_SUCCESS = 'success';
    const USER_CREATED_SUCCESS = 'new user created successfully';
    const USER_CREATED_FAILED = 'cannot create new user';
    const DEFAULT_UPDATE_SUCCESS = 'updated successfully';
    const DEFAULT_UPDATE_FAILED = 'cannot update';
    const PARKING_SPACE_CREATE_SUCCESS = 'new parking space added successfully';
    const PARKING_SPACE_CREATE_FAILED = 'cannot create new parking space';
    const USER_DELETE_ALLOWED = ':useraccount: successfully deleted';
    const PARKING_SPACE_DELETE_ALLOWED = 'parking space deleted successfully';
    const VEHICLE_CREATE_SUCCESS = 'new vehicle successfully created';
    const VEHICLE_DELETE_ALLOWED = 'vehicle deleted successfully';

    /**
     * LOGGING
     */
    const LOG_RESET_TOKEN_SUCCESS = 'token generated successfully';
    const LOG_RESET_TOKEN_FAIL = 'cannot generate reset token';

    /**
     * ERROR COPYWRITES
     */
    const USER_NOT_FOUND = 'user not found';
    const PARKING_SPACE_NOT_FOUND = 'parking space not found';
    const INVALID_CREDENTIALS = 'wrong email and password';
    const USER_DELETE_RESTRICT = ':useraccount: has transactions and cannot be deleted';
    const PARKING_SPACE_DELETE_RESTRICT = ':parkingspace: has transactions and cannot be deleted';
    const PARKING_SPACE_INVALID = 'invalid parking spaces';
    const VEHICLE_INVALID = 'invalid vehicle';
    const VEHICLE_NOT_FOUND = 'vehicle not found';
    const VEHICLE_DELETE_RESTRICT = ':vehicle: has transactions and cannot be deleted';

    /**
     * HTTP CODES
     */
    const HTTP_CODE_404 = 404; //not found code
    const HTTP_CODE_401 = 401; //unauthorized
    const HTTP_CODE_400 = 400; //bad request
    const HTTP_CODE_409 = 409; //conflict
    const HTTP_CODE_422 = 422;
    const HTTP_CODE_200 = 200; //success code
    const HTTP_CODE_201 = 201;

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

    /**
     * remember to put this on the app\config
     */
    const API_PREFIX = 'parking-api';

}
