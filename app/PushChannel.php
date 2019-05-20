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
     */
    public function createSubChannelRelationship(array $params)
    {
        try {
            $result = DB::table($this->subscriberChannelTable)->insert($params);

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
     * creates public push channel
     * @param array $params
     * @return mixed
     */
    public function createPublicChannel(array $params)
    {
        //create public channel
        try {

        }catch(Exception $e) {
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
     * create private push channel
     * @param array $params
     * @return mixed
     */
    public function createPrivateChannel(array $params)
    { }
}
