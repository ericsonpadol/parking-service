<?php

namespace App;

use App\CoreEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\CustomQueryBuilder;
use App\CustomLogger;
use App\Copywrite;
use DB;
use Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Message extends Model
{

    use SoftDeletes;

    //configuration
    protected $data = [
        'deleted_at'
    ];
    protected $table = 'messages';
    protected $messageStatusTable = 'messages_status';
    protected $userTable = 'users';
    protected $primaryKey = 'id';
    protected $fillable = [
        'message_type',
        'message',
        'to_user_id',
        'from_user_id',
    ];

    private $_logger = '';
    public static $messageType = [
        'incoming',
        'outgoing',
        'blast',
        'draft'
    ];

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        DB::connection()->enableQueryLog();
        $this->_logger = new Logger('Message');
        $this->_logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
    }

    /**
     * set message to unread
     * @param array $params
     * @return mixed
     */
    public function setToUnread(array $params)
    {
        try {

            $result = DB::table($this->messageStatusTable)->insertGetId($params);

            return $result;
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
     * set message to read
     * @param array $params
     * @return mixed
     */
    public function setToRead(array $params)
    {
        try {
            $result = DB::table($this->messageStatusTable)
                ->where('message_id', $params['message_id'])
                ->update([
                    'message_status' => 'read'
                ]);

            return $result;
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
     * create draft message
     * @param array $params
     * @return boolean
     */
    public function sendMessage(array $params)
    {
        //create incoming message
        $params['message_type'] = 'incoming';

        //get last
        $lastMsgId = $this->create($params)->id;

        //set the message to unread
        $msgParams = [
            'message_id' => $lastMsgId,
            'to_user_id' => $params['to_user_id'],
            'message_status' => 'unread'
        ];

        $unreadMsgResult = $this->setToUnread($msgParams);

        if (!$lastMsgId && !$unreadMsgResult) {
            return [
                'message' => Copywrite::SERVER_ERROR,
                'http_code' => Copywrite::HTTP_CODE_500,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ];
        }

        //create outgoing message
        $params['message_type'] = 'outgoing';
        if (!$this->create($params)) {
            return [
                'message' => Copywrite::SERVER_ERROR,
                'http_code' => Copywrite::HTTP_CODE_500,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ];
        }

        return [
            'message' => Copywrite::MESSAGE_SENT,
            'message_status' => $msgParams['message_status'],
            'http_code' => Copywrite::HTTP_CODE_200,
            'status' => Copywrite::RESPONSE_STATUS_SUCCESS
        ];
    }

    /**
     * returns user inbox messages
     * @param array $params
     * @return mixed
     */
    public function fetchMessageInbox(array $params)
    {
        $result = DB::table($this->table)
            ->join($this->messageStatusTable, $this->table . '.id', '=', $this->messageStatusTable . '.message_id')
            ->join($this->userTable, $this->table . '.from_user_id', '=', $this->userTable . '.id')
            ->select(
                $this->table . '.id',
                $this->table . '.message',
                $this->table . '.to_user_id',
                $this->table . '.from_user_id',
                $this->table . '.created_at',
                $this->messageStatusTable . '.message_status',
                $this->userTable . '.email',
                $this->userTable . '.full_name',
                $this->table . '.message_type'
            )
            ->where([
                [$this->table . '.to_user_id', '=', $params['to_user_id']],
                [$this->table . 'from_user_id', '=', $params['from_user_id']],
                [$this->table . '.message_type', '=', $params['message_type']]
            ])
            ->orderBy($this->table . '.created_at', 'asc')
            ->get();

        //application logging
        Log::info(CustomLogger::getConversationId() .
            CustomLogger::getCurrentRoute() .
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));
        Log::info(CustomLogger::getConversationId() .
            CustomLogger::getCurrentRoute() .
            CustomLogger::RESULT . serialize($result));

        //stream logging
        $this->_logger->addInfo(CustomLogger::getConversationId() .
            CustomLogger::getCurrentRoute() .
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));
        $this->_logger->addInfo(CustomLogger::getConversationId() .
            CustomLogger::getCurrentRoute() .
            CustomLogger::RESULT . serialize($result));

        return $result ? $result : [];
    }

    /**
     * return message outbox
     * @param array $params
     * @return mixed
     */
    public function fetchMessageOutbox(array $params)
    {
        $result = DB::table($this->table)
            ->join($this->userTable, $this->table . '.to_user_id', '=', $this->userTable . '.id')
            ->select(
                $this->table . '.id',
                $this->table . '.message',
                $this->table . '.to_user_id',
                $this->table . '.from_user_id',
                $this->table . '.created_at',
                $this->userTable . '.email',
                $this->userTable . '.full_name',
                $this->table . '.message_type'
            )
            ->where([
                [$this->table . '.from_user_id', '=', $params['from_user_id']],
                [$this->table . '.to_user_id', '=', $params['to_user_id']],
                [$this->table . '.message_type', '=', $params['message_type']]
            ])
            ->orderBy($this->table . '.created_at', 'asc')
            ->get();

        //application logging
        Log::info(CustomLogger::getConversationId() . ' ' .
            CustomLogger::getCurrentRoute() . ' ' .
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));
        Log::info(CustomLogger::getConversationId() . ' ' .
            CustomLogger::getCurrentRoute() . ' ' .
            CustomLogger::RESULT . serialize($result));

        //stream logging
        $this->_logger->addInfo(CustomLogger::getConversationId() . ' ' .
            CustomLogger::getCurrentRoute() . ' ' .
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));
        $this->_logger->addInfo(CustomLogger::getConversationId() . ' ' .
            CustomLogger::getCurrentRoute() . ' ' .
            CustomLogger::RESULT . serialize($result));

        return $result ? $result : [];
    }

    /**
     * return all messages
     * @param array $params
     * @return mixed
     */
    public function getAllMessage(array $params)
    {
        //fetch incoming messages
        $incomingParams = [
            'from_user_id' => $params['from_user_id'],
            'to_user_id' => $params['to_user_id'],
            'message_type' => 'incoming'
        ];
        $incomingMessages = $this->fetchAllInbox($incomingParams);

        //fetch outgoing messages
        $outgoingParams = [
            'from_user_id' => $params['from_user_id'],
            'to_user_id' => $params['to_user_id'],
            'message_type' => 'outgoing'
        ];
        $outgoingMessages = $this->fetchMessageOutbox($outgoingParams);

        $result = array_merge($incomingMessages, $outgoingMessages);

        //application logging
        Log::info(CustomLogger::getConversationId() . ' ' .
            CustomLogger::getCurrentRoute() . ' ' .
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));
        Log::info(CustomLogger::getConversationId() . ' ' .
            CustomLogger::getCurrentRoute() . ' ' .
            CustomLogger::RESULT . serialize($result));

        //stream logging
        $this->_logger->addInfo(CustomLogger::getConversationId() . ' ' .
            CustomLogger::getCurrentRoute() . ' ' .
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));
        $this->_logger->addInfo(CustomLogger::getConversationId() . ' ' .
            CustomLogger::getCurrentRoute() . ' ' .
            CustomLogger::RESULT . serialize($result));

        return $result ? $result : [];
    }

    /**
     * send public announcement messages / App Blast Messaging
     * @param array $params
     * @return mixed
     */
    public function createAnnouncement(array $params)
    {
        //create an announcement
    }

    /**
     * returns all user inbox messages
     * @param array $params
     * @return array $result
     */
    public function fetchAllInbox(array $params)
    {
        $result = DB::table($this->table)
            ->join($this->messageStatusTable, $this->table . '.id', '=', $this->messageStatusTable . '.message_id')
            ->join($this->userTable, $this->table . '.from_user_id', '=', $this->userTable . '.id')
            ->select(
                $this->table . '.id',
                $this->table . '.message',
                $this->table . '.to_user_id',
                $this->table . '.from_user_id',
                $this->table . '.created_at',
                $this->messageStatusTable . '.message_status',
                $this->userTable . '.email',
                $this->userTable . '.full_name',
                $this->table . '.message_type'
            )
            ->where([
                [$this->table . '.from_user_id', '=', $params['from_user_id']],
                [$this->table . '.to_user_id', '=', $params['to_user_id']],
                [$this->table . '.message_type', '=', $params['message_type']]
            ])
            ->orderBy($this->table . '.created_at', 'asc')
            ->get();

        //application logging
        Log::info(CustomLogger::getConversationId() . ' ' .
            CustomLogger::getCurrentRoute() . ' ' .
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));
        Log::info(CustomLogger::getConversationId() . ' ' .
            CustomLogger::getCurrentRoute() . ' ' .
            CustomLogger::RESULT . serialize($result));

        //stream logging
        $this->_logger->addInfo(CustomLogger::getConversationId() . ' ' .
            CustomLogger::getCurrentRoute() . ' ' .
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));
        $this->_logger->addInfo(CustomLogger::getConversationId() . ' ' .
            CustomLogger::getCurrentRoute() . ' ' .
            CustomLogger::RESULT . serialize($result));

        return $result ? $result : [];
    }

    /**
     * this method will get the user last messages.
     * @param array $params
     * @return mixed
     */
    public function getUserLastMessages(array $params)
    {
        $result = DB::table($this->table)
            ->join($this->messageStatusTable, $this->table . '.id', '=', $this->messageStatusTable . '.message_id')
            ->join($this->userTable, $this->table . '.to_user_id', '=', $this->userTable . '.id')
            ->select(
                $this->userTable . '.full_name',
                $this->userTable . '.email',
                $this->userTable . '.image_uri',
                $this->table . '.id',
                $this->table . '.message_type',
                $this->table . '.message',
                $this->table . '.to_user_id',
                $this->table . '.from_user_id',
                $this->table . '.created_at',
                $this->messageStatusTable . '.message_status'
            )
            ->where([
                [$this->table . '.from_user_id', '=', $params['user_id']]
            ])
            ->groupBy($this->table . '.to_user_id')
            ->orderBy($this->table . '.created_at', 'desc')
            ->get();

        //application logging
        Log::info(CustomLogger::getConversationId() . ' ' .
            CustomLogger::getCurrentRoute() . ' ' .
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));
        Log::info(CustomLogger::getConversationId() . ' ' .
            CustomLogger::getCurrentRoute() . ' ' .
            CustomLogger::RESULT . serialize($result));

        //stream logging
        $this->_logger->addInfo(CustomLogger::getConversationId() . ' ' .
            CustomLogger::getCurrentRoute() . ' ' .
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));
        $this->_logger->addInfo(CustomLogger::getConversationId() . ' ' .
            CustomLogger::getCurrentRoute() . ' ' .
            CustomLogger::RESULT . serialize($result));

        return $result ? $result : [];
    }

    /**
     * App Blast Message
     * @param array $param
     * @return mixed
     */
    public function createBlastMessage(array $params)
    {
        //get all users
        $allUsers = User::all();

        foreach ($allUsers as $user) {
            $params['to_user_id'] = $user->id;
            $lastMsg = $this->create($params)->id;

            $msgParams = [
                'message_id' => $lastMsg,
                'to_user_id' => $user->id,
                'message_status' => 'unread'
            ];

            $unreadMsgResult = $this->setToUnread($msgParams);

            if (!$lastMsg && !$unreadMsgResult) {
                return [
                    'message' => Copywrite::SERVER_ERROR,
                    'http_code' => Copywrite::HTTP_CODE_500,
                    'status' => Copywrite::RESPONSE_STATUS_FAILED
                ];
            }

            //create notification request here

        }

        return [
            'message' => Copywrite::MESSAGE_SENT,
            'http_code' => Copywrite::HTTP_CODE_200,
            'status' => Copywrite::RESPONSE_STATUS_SUCCESS
        ];
    }
}
