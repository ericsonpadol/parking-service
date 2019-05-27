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
    protected $table = 'core_events';
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        DB::connection()->enableQueryLog();
        $this->_logger = new Logger('CoreEvent');
        $this->_logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
    }

    /**
     * create a core event
     */
    public function createCoreEvent(array $params)
    {
        try {

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
}
