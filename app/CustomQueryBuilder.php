<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Copywrite;
use DB;

class CustomQueryBuilder extends Model
{
    /**
     * this function activates the reset password token
     */
    public function activatePasswordToken(array $params) {
        $queryTable = 'reset_password';

        //querystring to update the activation keys not to make it usable again.
        //$queryString = 'update ' . $queryTable . ' set activation = 1 where email = ?';

        try {
            $result = DB::table($queryTable)
                    ->where(['email' => $params['email']])
                    ->update(['activation' => 1]);

            if ($result) {
                return [
                    'status' => Copywrite::DEFAULT_UPDATE_SUCCESS,
                    'message' => Copywrite::LOG_RESET_TOKEN_SUCCESS
                ];
            } else {
                return [
                    'status' => Copywrite::DEFAULT_UPDATE_FAILED,
                    'message' => Copywrite::LOG_RESET_TOKEN_FAIL
                ];
            }
        } catch (Exception $e) {
            return [
                'message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'stack_trace' => $e->getTraceAsString(),
                'line' => $e->getLine()
            ];
        }
    }

    /**
     * this function returns the reset password details.
     */
    public function getResetPasswordDetails(array $params, $queryTable, array $customColumns) {
        $columns = implode(',', $customColumns);
        $whereClause = $params['where_clause'];

        $queryString = $whereClause
                ? 'select ' . $columns . ' from ' . $queryTable . ' where ' . $whereClause . ' order by created_at desc limit 1'
                : 'select ' . $columns . ' from ' . $queryTable . ' order by created_at desc limit 1';

        $result = DB::select($queryString);
        $count = 0;
        $resetObject = array();

        foreach ($result as $row) {
            if (property_exists($row, 'email') && property_exists($row, 'activation')) {
                $resetObject[$count] = ['email' => $row->email, 'activation' => $row->activation];
                $count++;
            }
        }

        $response = $resetObject ? $resetObject : '';

        return $response;
    }

    /**
     * This function is custom query for reset password logging
     * @param Array $params values to be inserted on the database.
     * @param Array $customColumns columns to be triggered on the database
     * @param String $queryTable table to be triggered
     * @return Array database trigger result
     */
    public function resetPasswordQuery(array $params, $queryTable, array $customColumns) {
        $columns = implode(',', $customColumns);

        //values modifier
        $columnCount = count($customColumns);

        $pdoValues = [];

        for ($a = 0; $a < $columnCount; $a++) {
            array_push($pdoValues, '?');
        }

        $pdoValuesString = implode(',', $pdoValues);

        $queryString = 'insert into ' . $queryTable . '(' . $columns . ') values(' . $pdoValuesString . ')';

        //execute DB command
        try {
            $result = DB::insert($queryString, $params);

            if ($result) {
                return [
                    'status' => Copywrite::DEFAULT_UPDATE_SUCCESS,
                    'message' => Copywrite::LOG_RESET_TOKEN_SUCCESS
                ];
            } else {
                return [
                    'status' => Copywrite::DEFAULT_UPDATE_FAILED,
                    'message' => Copywrite::LOG_RESET_TOKEN_FAIL
                ];
            }
        } catch (Exception $e) {
            return [
                'error_code' => $e->getCode(),
                'message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'line' => $e->getLine()
            ];
        }
    }

    /**
     * This function is custom query for reset password logging
     * @param Array $params values to be inserted on the database.
     * @param Array $customColumns columns to be triggered on the database
     * @param String $queryTable table to be triggered
     * @return Array database trigger result
     */
    public function loggerQuery(array $params, $queryTable, $customColumns) {
        $columns = implode(',', $customColumns);

        //values modifier
        $columnCount = count($customColumns);

        $pdoValues = [];

        for ($a = 0; $a < $columnCount; $a++) {
            array_push($pdoValues, '?');
        }

        $pdoValuesString = implode(',', $pdoValues);

        $queryString = 'insert into ' . $queryTable . '(' . $columns . ') values(' . $pdoValuesString . ')';

        try {
            $result = DB::insert($queryString, $params);

            if ($result) {
                return [
                    'status' => Copywrite::DEFAULT_UPDATE_SUCCESS,
                    'message' => Copywrite::LOGGER_INFO
                ];
            } else {
                return [
                    'status' => Copywrite::DEFAULT_UPDATE_FAILED,
                    'message' => Copywrite::LOGGER_ERROR
                ];
            }
        } catch (Exception $e) {
            return [
                'error_code' => $e->getCode(),
                'message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'line' => $e->getLine()
            ];
        }
    }

}
