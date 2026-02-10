<?php

namespace App\Repositories\Authentication;

use App\Interfaces\Authentication\RegisterInterface; 
use App\Models\User; 
use Illuminate\Support\Facades\DB;

class RegisterRepository implements RegisterInterface
{
    public function validateCompanyCode($companyCode)
    {
        return DB::table('company_progconf')
            ->where('comcde', $companyCode)
            ->where('appcde', env('APP_CODE', 'HR'))
            ->first();
    }
    // Insert a new user into the tenant users table
    public function registerUser(array $data)
    {
        return User::create([
            'username'       => $data['username'],          // Store username
            'password'       => bcrypt($data['password']),  // Hash password before saving
            'login_attempts' => 0,                           // Initialize failed attempts
            'login_counts'   => 0,                           // Initialize login count
            'is_locked'      => 0,                           // Account starts unlocked
        ]);
    }
}
