<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Copywrite;

class CreateParkingSpaceRequest extends Request
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [

        ];
    }

    public function response(array $errors) {
        return response()->json([
                    'message' => Copywrite::PARKING_SPACE_INVALID,
                    'code' => 422,
                    'trace_error' => [$errors],
                        ], 422);
    }

}
