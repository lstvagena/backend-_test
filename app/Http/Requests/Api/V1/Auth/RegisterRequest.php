<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    public function rules(): array
    {
        return [
            'company_code' => 'required',
            'username'     => 'required|unique:users,username',
            'password'     => 'required|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'company_code.required' => 'Company code is required',
            'username.required'     => 'Username is required',
            'username.unique'       => 'Username already exists',
            'password.required'     => 'Password is required',
            'password.min'          => 'Password must be at least 8 characters',
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
