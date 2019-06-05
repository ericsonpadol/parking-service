<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CustomQueryBuilder;
use App\CustomLogger;
use App\Copywrite;
use DB;
use Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\CoreEvent;

class AppNotification extends Model
{
    private $_logger = '';
    protected $table = 'core_events';
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        DB::connection()->enableQueryLog();
        $this->_logger = new Logger('CoreEvent');
        $this->_logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
    }

    public function createAppNotification(array $params)
    {

    }
}
