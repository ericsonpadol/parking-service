<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\CustomQueryBuilder;
use App\Copywrite;
use DB;

class User extends Authenticatable
{

    use SoftDeletes;

    protected $resetPasswordTable = 'reset_password';
    protected $dataResult = '';
    protected $userTable = 'users';
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
        'activation_token'
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
     * User & Vehicle Relationship
     */
    public function vehicles() {
        return $this->hasMany('App\Vehicle');
    }

    /**
     * User & Parkingspace Relationship
     */
    public function parkingspaces() {
        return $this->hasMany('App\ParkingSpace');
    }

    /**
     *
     */
    public function getUserVehicle($userId, $vehiclePlate) {
        $vehicle = User::find($userId)->vehicles()->where('plate_number', '=', $vehiclePlate)->get();

        return $vehicle;
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
                    'is_lock' => false
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

            if ($isLockCount->is_lock_count < 3) {
                //update the counter
                DB::table($table)->where('email', $params['email'])
                    ->increment('is_lock_count');

            } else {

                $lockoutTime = strtotime(date("H:i:s"))+1800; //15 minutes lockout period
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
                    'message' => Copywrite::ACCOUNT_ERROR,
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
}
