<?php
namespace App\Helpers;

class AuthHelper 
{
    public static function onSuccess(string $token, string $company, $user = null, string $action = 'login'): array
    {
        return [
            'status' => 'success',
            'message' => $action === 'register' ? 'Registration successful' : 'Login successful',
            'token' => $token,
            'company' => $company,
            ...(isset($user) ? ['user' => $user] : [])
        ];
    }

    //for register response
    public static function onSuccess2( ?string $token = null, ?string $company = null, $user = null, string $action = 'register'): array
    {
        return [
            'status' => 'success',
            'message' => 'Registration successful',
            'token' => $token,
            'company' => $company,
            ...(isset($user) ? ['user' => $user] : [])
        ];
    }

    public static function onError(string $key, bool $isGeneric = false, $extraData = null): array
    {
        $errors = [
            'user_not_found' => 'User not found',
            'invalid_credentials' => 'Invalid credentials',
            'company_not_found' => 'Company not found',
            'account_locked' => 'Account is locked',
        ];

        return [
            'status' => 'error',
            'message' => $isGeneric ? 'Authentication failed' : $errors[$key] ?? 'Authentication failed',
            ...(isset($extraData) ? ['data' => $extraData] : [])
        ];
    }

    public static function onLogout(): array
    {
        return [
            'status' => 'success',
            'message' => 'Logged out successfully',
            'action' => 'logout'
        ];
    }
}
