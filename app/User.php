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
                'line' => $e->getLine()
            ];
        }
    }

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
                'line' => $e->getLine()
            ];
        }
    }
}
