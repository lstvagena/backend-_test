<?php
namespace App\Services;

use App\Models\CentralUser;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

class TenantAuthService
{
    public function login(array $credentials): array
    {
        $centralUser = CentralUser::where('email', $credentials['email'])->first();

        if (!$centralUser) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        $company = Company::where('slug', $centralUser->company_slug)->firstOrFail();
        Config::set('database.connections.tenant.database', $company->database_name);
        DB::purge('tenant');
        DB::reconnect('tenant');

        $tenantUser = User::where('email', $credentials['email'])->first();
        
        if (!$tenantUser || !Hash::check($credentials['password'], $tenantUser->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }               

        $token = $tenantUser->createToken('api-token')->plainTextToken;

        return [
            'action' => 'login',
            'user' => $tenantUser,
            'token' => $token,
            'company' => $company->slug
        ];
    }

    public function register(array $data): array
    {
        $centralUser = CentralUser::create([
            'email' => $data['email'],
            'company_slug' => $data['company_slug'],
        ]);

        $company = Company::where('slug', $data['company_slug'])->firstOrFail();
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

        return [
            'action' => 'register',
            'user' => $tenantUser,
            'token' => $token,
            'company' => $company->slug
        ];
    }

    public function logout(): void
{
    // 1. Get company from route parameter (already in company.db middleware)
    $companySlug = request()->route('company');
    
    // 2. Switch to tenant DB BEFORE deleting token
    if ($companySlug) {
        $company = Company::where('slug', $companySlug)->firstOrFail();
        Config::set('database.connections.tenant.database', $company->database_name);
        DB::purge('tenant');
        DB::reconnect('tenant');
        User::setConnection('tenant'); // CRITICAL
    }
    
    // 3. Now delete token from CORRECT tenant DB
    $user = request()->user();
    if ($user && $user->currentAccessToken()) {
        $user->currentAccessToken()->delete();
    }
}

}
