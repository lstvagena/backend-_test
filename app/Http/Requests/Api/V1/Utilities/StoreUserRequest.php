<?php

namespace App\Http\Requests\Api\V1\Utilities;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_type_id' => 'required|exists:user_types,id',
            'username'     => 'required|unique:users,username',
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:6',
        ];
    }

    public function messages()
    {
        return [
            'user_type_id.required' => 'User type is required.',
            'user_type_id.exists'   => 'Selected user type does not exist.',

            'username.required'     => 'Username is required.',
            'username.unique'       => 'Username already exists.',

            'name.required'         => 'Name is required.',
            'name.string'           => 'Name must be a valid string.',
            'name.max'              => 'Name may not exceed 255 characters.',

            'email.required'        => 'Email is required.',
            'email.email'           => 'Email must be a valid email address.',
            'email.unique'          => 'Email already exists.',

            'password.required'     => 'Password is required.',
            'password.string'       => 'Password must be a valid string.',
            'password.min'          => 'Password must be at least 6 characters.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
