<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

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
            //
            'city' => 'required|max:255',
            'establishment_type' => 'required',
            'parking_slot' => 'required|string'
        ];
    }

    public function response(array $errors) {
        return response()->json([
                    'message' => 'Invalid Parking Space',
                    'code' => 422,
                    'trace_error' => [$errors],
                        ], 422);
    }

}
