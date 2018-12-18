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
    const USER_DELETE_ALLOWED = 'user successfully deleted';
    const PARKING_SPACE_DELETE_ALLOWED = 'parking space deleted successfully';
    /**
     * ERROR COPYWRITES
     */
    const USER_NOT_FOUND = 'user not found';
    const PARKING_SPACE_NOT_FOUND = 'parking space not found';
    const INVALID_CREDENTIALS = 'wrong email and password';
    const USER_DELETE_RESTRICT = ':useraccount: has transactions and cannot be deleted';
    const PARKING_SPACE_DELETE_RESTRICT = ':parkingspace: has transactions and cannot be deleted';

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
     * remember to put this on the app\config
     */
    const API_PREFIX = 'parking-api';
 }
