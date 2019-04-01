<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Session;
use Route;

class CustomLogger extends Model
{
    //copywrites for logging
    const DB_CALL = 'DB_Call:';
    const RESULT = 'RESULT:';
    const CONVERSATION_ID = 'ConversationId=';
    const ACTION_ROUTE = 'ACTION_ROUTE=';

    public static function getConversationId() {
        return self::CONVERSATION_ID . Session::getId();
    }

    public static function getCurrentRoute() {
        return self::ACTION_ROUTE . Route::getCurrentRoute()->getActionName();
    }

}
