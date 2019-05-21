<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\User;
use App\CustomQueryBuilder;
use App\CustomLogger;
use App\Copywrite;
use DB;
use Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class PushChannel extends Model
{
    protected $table = 'push_channels';
    protected $subscriberChannelTable = 'subscribers_channels';
    protected $primaryKey = 'id';
    private $_logger = '';
    public $timestamps = true;

    protected $fillable = [
        'channel_name',
        'channel_type',
        'ch_desc',
        'created_by'
    ];

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        DB::connection()->enableQueryLog();
        $this->_logger = new Logger('PushChannel');
        $this->_logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
    }

    /**
     * subscriber / channel relationship
     * @param array $params
     * @return mixed
     */
    public function createSubChannelRelationship(array $params)
    {
        try {
            //check first if user is already subscribe to the channel
            $getSubChannel = DB::table($this->subscriberChannelTable)
                ->where([
                    ['channel_id', '=', $params['channel_id']],
                    ['user_id', '=', $params['user_id']]
                ])
                ->first();

            if ($getSubChannel) {
                return [
                    'message' => Copywrite::CHANNEL_SUBSCRIBER_SUBCRIBED,
                    'http_code' => Copywrite::HTTP_CODE_400,
                    'status' => Copywrite::RESPONSE_STATUS_FAILED
                ];
            }

            $result = DB::table($this->subscriberChannelTable)->insert($params);

            //log
            Log::info(CustomLogger::getCurrentRoute() .
                CustomLogger::getConversationId() .
                CustomLogger::DB_CALL . serialize(DB::getQueryLog()));

            $this->_logger->addInfo(CustomLogger::getCurrentRoute() .
                CustomLogger::getConversationId() .
                CustomLogger::DB_CALL . serialize(DB::getQueryLog()));

            return [
                'message' => Copywrite::CHANNEL_SUBSCRIBER_SUCCESS,
                'http_code' => Copywrite::HTTP_CODE_200,
                'status' => Copywrite::RESPONSE_STATUS_SUCCESS
            ];
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
     * this method will get the list of user's subcribed channels
     * @param array $params
     * @return array
     */
    public function getSubscriberPushChannel(array $params)
    {
        $result = DB::table($this->table)
            ->join($this->subscriberChannelTable, $this->table . '.id', '=', $this->subscriberChannelTable . '.channel_id')
            ->select($this->table . '.*')
            ->where(array([$this->subscriberChannelTable . '.user_id', '=', $params['user_id']]))
            ->get();

        //application log
        Log::info(CustomLogger::getConversationId() .' '.
            CustomLogger::getCurrentRoute() .' '.
            CustomLogger::DB_CALL . serialize(DB::getQueryLog()));
        Log::info(CustomLogger::getConversationId() .' '.
            CustomLogger::getCurrentRoute() .' '.
            CustomLogger::RESULT .' '. serialize($result));

        //stream logging
        $this->_logger->addInfo(CustomLogger::getConversationId() .' '.
            CustomLogger::getCurrentRoute() .' '.
            CustomLogger::DB_CALL .' '. serialize(DB::getQueryLog()));
        $this->_logger->addInfo(CustomLogger::getConversationId() .' '.
            CustomLogger::getCurrentRoute() .' '.
            CustomLogger::RESULT .' '. serialize($result));

        if (!$result) {
            return [
                'message' => Copywrite::DEFAULT_NO_ENTRY_FOUND,
                'http_code' => Copywrite::HTTP_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ];
        }

        return [
            'data' => $result,
            'http_code' => Copywrite::HTTP_CODE_200,
            'status' => Copywrite::RESPONSE_STATUS_SUCCESS
        ];
    }
}
