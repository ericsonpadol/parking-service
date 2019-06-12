<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Copywrite;
use DB;

class CustomQueryBuilder extends Model
{

    public static function getParkingSpaceDistance($fromLat, $fromLon, $earthRadius = 6371)
    {
        $queryTable = 'parkingspaces';

        $distance = 'ROUND(( ' . $earthRadius . ' * acos( cos( RADIANS( ' . $fromLat . ') ) * '
            . 'cos( RADIANS( ' . $queryTable . '.space_lat) ) *'
            . 'cos( radians( ' . $queryTable . '.space_lon) - RADIANS(' . $fromLon . ') ) + '
            . 'sin( RADIANS( ' . $fromLat . ') ) *'
            . 'sin( RADIANS(' . $queryTable . '.space_lat) ) ) ), 4) AS distance';

        return $distance;
    }

    /**
     *  this query string will get the nearest parking space of the user provided by the latitude and longtitude
     *  @param Double $fromLang : user selected latitude
     *  @param Double $fromLot : user selected longtitude
     *  @param Decimal $earthRadius : this is a constant value if earthRadius is KM use 6371
     *      else if earthRadius is Miles use 3959
     *  @param Decimal $precision : is the border radius of all returned value to the user, this is default to 5 KM/MILES.
     */

    public function getNearbyParkingSpaces($fromLat, $fromLon, $earthRadius = 6371, $precision = 5)
    {
        $queryTable = 'parkingspaces';
        $joinTable = 'parkspace_pricing';
        $mainColumn = [
            $queryTable . '.id',
            $queryTable . '.address',
            $queryTable . '.city',
            $queryTable . '.parking_slot',
            $queryTable . '.building_name',
            $queryTable . '.establishment_type',
            $queryTable . '.description',
            $queryTable . '.image_uri',
            $queryTable . '.space_lat',
            $queryTable . '.space_lon'
        ];

        $joinColumn = [
            $joinTable . '.pspace_base_price',
            $joinTable . '.pspace_calc_price',
            $joinTable . '.avail_start_datetime',
            $joinTable . '.avail_end_datetime'
        ];

        $mainColumnString = implode(',', $mainColumn);
        $joinColumnString = implode(',', $joinColumn);

        try {

            $distance = '( ' . $earthRadius . ' * acos( cos( RADIANS( ' . $fromLat . ') ) * '
                . 'cos( RADIANS( ' . $queryTable . '.space_lat) ) *'
                . 'cos( radians( ' . $queryTable . '.space_lon) - RADIANS(' . $fromLon . ') ) + '
                . 'sin( RADIANS(' . $fromLat . ') ) *'
                . 'sin( RADIANS(' . $queryTable . '.space_lat) ) ) ) AS distance';
            $from = 'FROM ' . $queryTable . ',' . $joinTable;
            $where = 'WHERE ' . $queryTable . '.id = ' . $joinTable . '.parking_space_id' .
                ' AND ' . $queryTable . '.status = "active" ';
            $having = 'HAVING DISTANCE < ' . $precision;
            $orderLimit = 'ORDER BY ' . $joinTable . '.avail_start_datetime ASC,  distance ASC LIMIT 0, 10'; //limit the result to 10

            $queryString = "SELECT " . $mainColumnString . ', ' . $joinColumnString . ', ' . $distance . ' ' . $from . ' ' . $where
                . ' ' . $having . ' ' . $orderLimit;

            return $queryString;
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
     * this function activates the reset password token
     */
    public function activatePasswordToken(array $params)
    {
        $queryTable = 'reset_password';

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
    public function getResetPasswordDetails(array $params, $queryTable, array $customColumns)
    {
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
    public function resetPasswordQuery(array $params, $queryTable, array $customColumns)
    {
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
    public function loggerQuery(array $params, $queryTable, $customColumns)
    {
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
