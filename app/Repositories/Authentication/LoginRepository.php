<?php

namespace App\Repositories\Authentication;

use App\Interfaces\Authentication\LoginInterface;
use App\Models\User;
use App\Models\Utilities\BaselineSecurity\SecurityParameter;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class LoginRepository implements LoginInterface
{
    public function validateCompanyCode($companyCode)
    {
        return DB::table('company_progconf')
            ->where('comcde', $companyCode)
            ->where('appcde', env('APP_CODE', 'HR'))
            ->first();
    }

    public function authenticateUser($username)
    {
        return User::where('username', $username)->first();
    }

    public function incrementLoginAttempts($username)
    {
        return User::where('username', $username)->increment('login_attempts');
    }

    public function getSecurityParameters()
    {
        return SecurityParameter::first();
    }

    public function lockAccount($username) {
        return User::where('username', $username)->update(['is_locked' => 1]);
    }

    public function updateLoginInformation($username) {
        return User::where('username', $username)->update([
            'login_counts' => DB::raw('login_counts + 1'),
            'last_login' => Carbon::now(),
            'login_attempts' => 0
        ]);
    }

    public function getPersonalAccessToken($token) {
        return PersonalAccessToken::findToken($token);
    }
}
