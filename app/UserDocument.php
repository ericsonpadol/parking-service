<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Copywrite;
use App\User;
use DB;
use Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class UserDocument extends Model
{
    //configuration
    use SoftDeletes;

    protected $table = 'users_documents';
    protected $primaryKey = 'id';
    private $_logger = '';

    //mass assignable attributes
    protected $fillable = [
        'docu_type',
        'docu_title',
        'docu_uri',
        'docu_desc',
        'user_id',
        'docu_encrypt',
        'user_message',
    ];

    protected $date = [
        'deleted_at'
    ];

    //constructor
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        DB::connection()->enableQueryLog();
        $this->_logger = new Logger('user-module');
        $this->_logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
    }

    /**
     * User & UserDocument Relationship
     * @return Collection
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
