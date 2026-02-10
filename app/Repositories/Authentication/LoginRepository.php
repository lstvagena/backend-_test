<?php

namespace App\Repositories\Authentication;

use App\Interfaces\Authentication\LoginInterface; // Login contract
use App\Models\User; // User model
use App\Models\Utilities\BaselineSecurity\SecurityParameter; // Security config model
use Laravel\Sanctum\PersonalAccessToken; // Sanctum token model
use Carbon\Carbon; // Date/time helper
use Illuminate\Support\Facades\DB; // DB facade

class LoginRepository implements LoginInterface
{
    // Validate company code against central configuration table
    public function validateCompanyCode($companyCode)
    {
        return DB::table('company_progconf')
            ->where('comcde', $companyCode)              // Match company code
            ->where('appcde', env('APP_CODE', 'HR'))     // Match application code
            ->first();                                   // Return single record
    }

    // Retrieve user record by username
    public function authenticateUser($username)
    {
        return User::where('username', $username)->first();
    }

    // Increment failed login attempt counter
    public function incrementLoginAttempts($username)
    {
        return User::where('username', $username)
            ->increment('login_attempts');
    }

    // Get global security parameters (login limits, lock rules, etc.)
    public function getSecurityParameters()
    {
        return SecurityParameter::first();
    }

    // Lock user account
    public function lockAccount($username)
    {
        return User::where('username', $username)
            ->update(['is_locked' => 1]);
    }

    // Update successful login metadata
    public function updateLoginInformation($username)
    {
        return User::where('username', $username)->update([
            'login_counts'   => DB::raw('login_counts + 1'), // Increment login count
            'last_login'     => Carbon::now(),               // Store last login time
            'login_attempts' => 0                             // Reset failed attempts
        ]);
    }

    // Retrieve Sanctum token using raw token value
    public function getPersonalAccessToken($token)
    {
        return PersonalAccessToken::findToken($token);
    }
}
