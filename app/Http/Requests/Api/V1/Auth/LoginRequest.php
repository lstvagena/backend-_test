<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'company_code' => 'required',
      'username' => 'required',
      'password' => 'required'
    ];
  }

  public function messages(): array
  {
    return [
      'company_code.required' => 'Company code is required',
      'username.required' => 'Username is required',
      'password.required' => 'Password is required',
    ];
  }

  protected function failedValidation(Validator $validator)
  {
    throw new HttpResponseException(
      response()->json([
        'status' => 'error',
        'message' => 'Validation failed',
        'errors' => $validator->errors()
      ], 422)
    );
  }
}
