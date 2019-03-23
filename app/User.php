<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\CustomQueryBuilder;
use App\Copywrite;
use App\AccountSecurity;
use DB;
use Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class User extends Authenticatable
{

    use SoftDeletes;

    protected $resetPasswordTable = 'reset_password';
    protected $primaryKey = 'id';
    protected $dataResult = '';
    protected $userTable = 'users';
    protected $table = 'users';
    protected $tblVehicle = 'vehicles';
    private $_logger = '';
    protected $resetPasswordColumns = [
        'email', 'reset_token'
    ];
    protected $date = [
        'deleted_at'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'password',
        'mobile_number',
        'full_name',
        'is_activated',
        'is_lock',
        'is_lock_count',
        'activation_token',
        'image_uri'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    /**
     * User Constructor
     */
    public function __construct(array $attributes = []) {
        parent::__construct($attributes);

        DB::connection()->enableQueryLog();
        $this->_logger = new Logger('user-module');
        $this->_logger->pushHandler(new StreamHandler('php://stderr', Logger::INFO));
    }

    /**
     * User & Vehicle Relationship
     * @return Object
     */
    public function vehicles() {
        return $this->hasMany('App\Vehicle');
    }

    /**
     * User & Parkingspace Relationship
     *
     * @return Object
     */
    public function parkingspaces() {
        return $this->hasMany('App\ParkingSpace');
    }

    /**
     * get user specific vehicle
     *
     * @return Array
     */
    public function getUserVehicle($userId, $vehicleId) {
        $vehicle = User::find($userId)->vehicles()->where('id', '=', $vehicleId)->get();

        /**
         * @EBP 03162019 : looking at array index 0 is ok since, the return is expecting 1 data always
         */
        if ($vehicle->isEmpty()) {
            return [
                'message' => Copywrite::VEHICLE_NOT_FOUND,
                'http_code' => Copywrite::HTTP_CODE_404,
                'status' => Copywrite::RESPONSE_STATUS_FAILED
            ];
        }

        return [
            'data' => $vehicle
        ];
    }

    public function resetPassword(array $params, array $columns) {
        try {

            $result = User::where($columns)->update($params);

            $response = $result > 0
                    ? Copywrite::RESPONSE_STATUS_SUCCESS
                    : Copywrite::RESPONSE_STATUS_FAILED;

            return $response;

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
     *
     */
    public static function unlockAccount(array $params = [], $table = 'users') {
        try {

            DB::table($table)->where('email', $params['email'])
                ->update([
                    'is_lock_count' => 0,
                    'is_lock' => 'false'
                ]);

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
     *
     */
    public static function setLockCounter(array $params = [], $table = 'users') {
        try {
            //get the lock counter and check if it is 3

            $isLockCount = DB::table($table)->where([
                ['email', $params['email']]
            ])->first();

            if (!$isLockCount) {
                return false;
            }

            if ($isLockCount->is_lock_count < 2) {
                //update the counter
                DB::table($table)->where('email', $params['email'])
                    ->increment('is_lock_count');

            } else {

                $lockoutTime = strtotime(date("H:i:s"))+120; //30 minutes lockout period
                $lockoutPeriod = date('H:i:s', $lockoutTime);

                //lock the account
                DB::table($table)->where('email', $params['email'])
                    ->update([
                        'is_lock' => 'true',
                        'is_lock_count' => 0,
                        'lockout' => $lockoutPeriod,
                    ]);
            }

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
     *
     */
    public static function isAccountActive(array $params = [], $table = 'users') {
        try {

            $result = DB::table($table)->where([
                ['email', $params['email']]
            ])->first();

            if(!$result) {
                return $result;
            }

            if ($result->is_lock == 'true' && strtotime($result->lockout) < strtotime(date('H:i:s'))) {
                //reset the lockout counter and reset the is lock identifier and reset the time
                DB::table($table)->where('email', $params['email'])
                    ->update([
                        'is_lock' => 'false',
                        'is_lock_count' => 0,
                        'lockout' => '00:00:00'
                    ]);
            }

            if ($result->is_lock == 'true' || $result->is_activated == 'false') {
                return [
                    'message' => $result->is_lock == 'true' ? Copywrite::ACCOUNT_ERROR : Copywrite::ACCOUNT_ACTIVATION_ERROR,
                    'status_code' => Copywrite::STATUS_CODE_106,
                    'status' => Copywrite::RESPONSE_STATUS_FAILED,
                    'http_code' => Copywrite::HTTP_CODE_401
                ];
            }

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
     *
     */
    public static function verifyUserAccount(array $params = [], $table = 'users') {
        try {
            $result = DB::table($table)->where([
                ['activation_token', $params['active']],
                ['is_activated', 'false']])->first();

            //if result returns null break the verification flow
            if (!$result) {
                return [
                    'result' => $result,
                    'message' => Copywrite::ACTIVATION_CODE_FAIL,
                    'activation' => Copywrite::ACTIVATION_STATUS_FAIL,
                    'status_code' => Copywrite::STATUS_CODE_102,
                    'status' => Copywrite::RESPONSE_STATUS_FAILED
                ];
            }

            //continue activation flow
            //activate account


            $activate = DB::table($table)->where('id', $result->id)->update(['is_activated' => 'true']);

            if (!$activate) {
                return [
                    'result' => $activate,
                    'message' => Copywrite::ACTIVATION_CODE_FAIL,
                    'activation' => Copywrite::ACTIVATION_STATUS_FAIL,
                    'status_code' => Copywrite::STATUS_CODE_102,
                    'status' => Copywrite::RESPONSE_STATUS_FAILED
                ];
            }

            return [
                'result' => $result,
                'status_code' => Copywrite::STATUS_CODE_105,
                'status' => Copywrite::DEFAULT_UPDATE_SUCCESS
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

    /***
     *
     */
    public function setAnswerSecurityQuestions(array $params = [],
        array $customColumns = [], $table = 'accountsecurity_user') {
        try {
            $columns = implode(',', $customColumns);

            $columnCount = count($customColumns);

            $pdoValues = [];

            for ($a=0; $a < $columnCount; $a++) {
                array_push($pdoValues, '?');
            }

            $pdoValuesString = implode(',', $pdoValues);

            $sql = 'insert into ' . $table . ' ( ' . $columns . ' ) values (' . $pdoValuesString . ') ';

            $result = DB::insert($sql, $params);

            if (!$result) {
                return [
                    'status' => Copywrite::RESPONSE_STATUS_FAILED,
                    'status_code' => Copywrite::STATUS_CODE_500,
                    'http_code' => Copywrite::HTTP_CODE_500,
                    'message' => Copywrite::SERVER_DOWNTIME
                ];
            }

            return [
                'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                'status_code' => Copywrite::STATUS_CODE_200,
                'http_code' => Copywrite::HTTP_CODE_200,
                'message' => Copywrite::SECURITY_QUESTION_SUCCESS
            ];

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
     *
     */
    public function getSecurityQuestions(array $params = [],
        $table = 'accountsecurity_user',
        $tblAccountSec = 'accountsecurities') {
        try {

            $result = DB::table($table)
                ->join($tblAccountSec, $table . '.secques_id', '=', $tblAccountSec . '.sec_id')
                ->select($table . '.*', $tblAccountSec . '.value')
                ->where('user_id', $params['user_id'])
                ->get();

            if (!$result) {
                return [
                    'status' => Copywrite::RESPONSE_STATUS_FAILED,
                    'status_code' => Copywrite::HTTP_CODE_404,
                    'http_code' => Copywrite::HTTP_CODE_404,
                    'message' => Copywrite::USER_NOT_FOUND
                ];
            }

            return [
                'data' => $result,
                'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                'http_code' => Copywrite::HTTP_CODE_200,
                'status_code' => Copywrite::STATUS_CODE_200
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
     * verify user security questions
     *
     * @param Array $params
     * @param String $table
     * @return Array
     */
    public function verifySecurityQuestions(array $params = [],
        $table = 'accountsecurity_user',
        $userTbl = 'users',
        $accountsecTbl = 'accountsecurities') {
        try {

            $whereClause = array(
                [$table . '.user_id', '=', $params['user_id']],
                [$table . '.secques_id', '=', $params['secques_id']],
                [$table . '.answer_value', '=', md5($params['answer_value'])]
            );

            $result = DB::table($table)
                ->join($userTbl, $userTbl . '.id', '=',  $table . '.user_id')
                ->select($userTbl . '.id', $userTbl . '.email', $userTbl . '.full_name', $table . '.user_id')
                ->where($whereClause)
                ->get();
            //stream logging
            $this->_logger->addInfo('Verify Question Query : ' . serialize(DB::getQueryLog()));
            $this->_logger->addInfo('Verify Question Result:' . serialize($result));

            //local logging
            Log::info('Verify Question Query:', DB::getQueryLog());
            Log::info('Verify Question Result:', $result);

            if (!$result) {
                //get wrong security question id
                $secques = DB::table($accountsecTbl)
                    ->select('value')
                    ->where('sec_id', $params['secques_id'])
                    ->get();

               if (!$secques) {
                    return [
                        'status' => Copywrite::RESPONSE_STATUS_FAILED,
                        'status_code' => Copywrite::STATUS_CODE_109,
                        'http_code' => Copywrite::HTTP_CODE_404,
                        'message' => Copywrite::ACCOUNT_SECURITY_QUESTION_NOT_FOUND
                    ];
               }

                $toReplace = ['/:secques:/'];
                //@EBP 03092019: this will result only to one wrong answer so forcing to look into array index[0] should not break any logic
                $fromReplace = [$secques[0]->value];
                $copyString = preg_replace($toReplace, $fromReplace, Copywrite::INVALID_ANSWER_SECURITY_QUESTIONS);
                return [
                    'status' => Copywrite::RESPONSE_STATUS_FAILED,
                    'status_code' => Copywrite::STATUS_CODE_109,
                    'http_code' => Copywrite::HTTP_CODE_400,
                    'message' => $copyString
                ];
            }

            return [
                'data' => $result,
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
}
