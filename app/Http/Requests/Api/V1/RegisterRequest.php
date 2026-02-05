<?php
namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:central_users',
            'password' => 'required|min:8',
            'company_slug' => 'required|exists:companies,slug',
        ];
    }
}
