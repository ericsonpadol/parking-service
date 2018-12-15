<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class LoginAuthenticateRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'email' => 'required|email|max:255',
            'password' => 'required',
        ];
    }

    public function response(array $errors) {
        return response()->json([
            'message' => 'Invalid User Account',
            'code' => 422,
            'trace_error' => [$errors],
        ], 422);
    }
}
