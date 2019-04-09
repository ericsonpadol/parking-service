<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Session;
use App\CustomQueryBuilder;

class ParkingAuditLog extends Model
{
    protected $conversationId = '';

    protected $table = 'parking_audit_logs';

    protected $fillable = [
        'conv_id',
        'status',
        'action',
        'log',
        'user_id'
    ];

    public function __construct()
    {
        $this->conversationId = Session::getId();
    }

    public function auditLogger(array $params) {
        $queryBuilder = new CustomQueryBuilder();

        $columns = [
            'conv_id',
            'status',
            'action',
            'log',
            'user_id'
        ];

        $queryParams = [
            $this->conversationId,
            $params['status'],
            $params['action'],
            $params['log'],
            $params['user_id']
        ];

        $queryBuilder->loggerQuery($queryParams, $this->table, $columns);

        return $this->conversationId;
    }

}
