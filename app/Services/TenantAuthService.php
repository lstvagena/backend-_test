<?php
namespace App\Services;

use App\Helpers\AuthHelper;
use App\Models\CentralUser;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantAuthService 
{
    public function login(array $credentials): array
    {
        $centralUser = CentralUser::where('email', $credentials['email'])->first();

        if (!$centralUser) {
            return AuthHelper::onError('user_not_found');
        }

        $company = Company::where('slug', $centralUser->company_slug)->first();
        if (!$company) {
            return AuthHelper::onError('company_not_found');
        }

        Config::set('database.connections.tenant.database', $company->database_name);
        DB::purge('tenant');
        DB::reconnect('tenant');

        $tenantUser = User::where('email', $credentials['email'])->first();
        
        if (!$tenantUser || !Hash::check($credentials['password'], $tenantUser->password)) {
            return AuthHelper::onError('invalid_credentials');
        }               

        $token = $tenantUser->createToken('api-token')->plainTextToken;

        return AuthHelper::onSuccess($token, $company->slug, $tenantUser, 'login');
    }

    public function register(array $data): array
    {
        try {
            $centralUser = CentralUser::create([
                'email' => $data['email'],
                'company_slug' => $data['company_slug'],
            ]);

            $company = Company::where('slug', $data['company_slug'])->first();
            if (!$company) {
                return AuthHelper::onError('company_not_found');
            }

            Config::set('database.connections.tenant.database', $company->database_name);
            DB::purge('tenant');
            DB::reconnect('tenant');

            $tenantUser = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $centralUser->update(['tenant_user_id' => $tenantUser->id]);
            $token = $tenantUser->createToken('api-token')->plainTextToken;

            return AuthHelper::onSuccess($token, $company->slug, $tenantUser, 'register');
        } catch (\Exception $e) {
            return AuthHelper::onError('registration_failed');
        }
    }

    public function logout(): array
    {
        $user = request()->user();
        
        if (!$user || !$user->currentAccessToken()) {
            return AuthHelper::onError('no_active_session');
        }
        
        $tokenId = $user->currentAccessToken()->id;
        $user->setConnection('tenant');
        $user->tokens()->where('id', $tokenId)->delete();
        
        return AuthHelper::onLogout();
    }
}
