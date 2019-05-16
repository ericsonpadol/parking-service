<?php

namespace App;

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
    public function setToUnread(array $params) {
        try {

            $result = DB::table($this->messageStatusTable)->insertGetId($params);

            return $result;

        } catch(Exception $e) {
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
    public function setToRead(array $params) {
        try {
            $result = DB::table($this->messageStatusTable)
                ->where('message_id', $params['message_id'])
                ->update([
                    'message_status' => 'read'
                ]);

            return $result;
        } catch(Exception $e) {
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

        if(!$lastMsgId && !$unreadMsgResult) {
            return [
                'message' => Copywrite::SERVER_ERROR,
                'http_code' => Copywrite::HTTP_CODE_500,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ];
        }

        //create outgoing message
        $params['message_type'] = 'outgoing';
        if(!$this->create($params)) {
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
    public function fetchMessageInbox(array $params) {
        $userTable = 'users';

        $result = DB::table($this->table)
            ->join($this->messageStatusTable, $this->table . '.id', '=', $this->messageStatusTable . '.message_id')
            ->join($userTable, $this->table . '.to_user_id', '=', $userTable . '.id')
            ->select(
                $this->table . '.id',
                $this->table . '.message',
                $this->table . '.to_user_id',
                $this->table . '.from_user_id',
                $this->table . '.created_at',
                $this->messageStatusTable . '.message_status',
                $userTable . '.email',
                $userTable . '.full_name'
            )
            ->where([
                [$this->table . '.to_user_id' , '=', $params['to_user_id']],
                [$this->table . '.message_type', '=', $params['message_type']]
            ])
            ->orderBy($this->table . '.created_at', 'desc')
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
}
