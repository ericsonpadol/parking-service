<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CustomQueryBuilder;
use App\Copywrite;
use DB;

class AccountSecurity extends Model
{

    protected $table = 'accountsecurities';
    protected $secQuesTable = 'accountsecurity_user';

    /**
     * This function gets the list of account security questions created by the user.
     *
     * @param Int $id : UserID assigned to the security question
     * @return Array
     */
    public function getAccountSecurityQuestions($id) {
        try{

            $listSecurityQuestions = DB::table($this->secQuesTable)
            ->join($this->table, $this->secQuesTable . '.secques_id', '=', $this->table . '.sec_id')
            ->where($this->secQuesTable . '.user_id', $id)
            ->get();

            if (!$listSecurityQuestions) {
                return [
                    'message' => Copywrite::ACCOUNT_SECURITY_QUESTION_NOT_FOUND,
                    'status' => Copywrite::RESPONSE_STATUS_FAILED,
                    'http_code' => Copywrite::HTTP_CODE_404,
                    'status_code' => Copywrite::STATUS_CODE_404
                ];
            }

            //limit the return of the value
            $data = array();
            $list = array();


            foreach($listSecurityQuestions as $key) {
                $data = [
                    'secques_id' => $key->secques_id,
                    'user_id' => $key->user_id,
                    'updated_at' => $key->updated_at,
                    'value' => $key->value,
                    'description' => $key->description
                ];
                array_push($list, $data);
            }

            return [
                'data' => $list ? $list : [],
                'status' => Copywrite::RESPONSE_STATUS_SUCCESS,
                'http_code' => Copywrite::HTTP_CODE_200
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

}
