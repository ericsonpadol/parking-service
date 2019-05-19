<?php

namespace App;

use App\User;
use App\CustomQueryBuilder;
use App\CustomLogger;
use App\Copywrite;
use DB;
use Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Illuminate\Database\Eloquent\Model;

class PushChannel extends Model
{
    private $_logger = '';

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        DB::connection()->enableQueryLog();
        $this->_logger = new Logger('PushChannel');
        $this->_logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
    }

    /**
     * creates public push channel
     * @param array $params
     * @return mixed
     */
    public function createPublicChannel(array $params)
    {

    }

    /**
     * creates presence push channel
     * @param array $params
     * @return mixed
     */
    public function createPresenceChannel(array $params)
    {

    }

    /**
     * create private push channel
     * @param array $params
     * @return mixed
     */
    public function createPrivateChannel(array $params)
    {

    }
}
