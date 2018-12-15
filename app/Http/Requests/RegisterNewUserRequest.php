<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class RegisterNewUserRequest extends Request
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
            'mobile_number' => 'required',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required',
            'full_name' => 'required',
        ];
    }

    public function response(array $errors) {
        return response()->json([
            'message' => 'Invalid Account',
            'code' => 422,
            'trace_error' => [$errors],
        ], 422);
    }

}
