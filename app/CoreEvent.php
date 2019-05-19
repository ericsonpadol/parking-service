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

class CoreEvent extends Model
{
    private $_logger = '';

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        DB::connection()->enableQueryLog();
        $this->_logger = new Logger('CoreEvent');
        $this->_logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
    }

}
