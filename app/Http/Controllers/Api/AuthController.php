<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CentralUser;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:central_users',
            'password' => 'required|min:8',
            'company_slug' => 'required|exists:companies,slug',  // company1, company2
        ]);

        // 1. Create central user record (main_db)
        $centralUser = CentralUser::create([
            'email' => $request->email,
            'company_slug' => $request->company_slug,
        ]);

        // 2. Switch to company database
        $company = Company::where('slug', $request->company_slug)->firstOrFail();
        config(['database.connections.tenant.database' => $company->database_name]);
        DB::purge('tenant');
        DB::reconnect('tenant');

        // 3. Create tenant user
        $tenantUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 4. Link tenant user ID back to central
        $centralUser->update(['tenant_user_id' => $tenantUser->id]);

        // 5. Generate token
        $token = $tenantUser->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $tenantUser,
            'token' => $token,
            'company' => $company->slug
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 1. Find user in MAIN DB first
        $centralUser = CentralUser::where('email', $request->email)->first();
        if (!$centralUser) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        // 2. Switch to company database
        $company = Company::where('slug', $centralUser->company_slug)->firstOrFail();
        config(['database.connections.tenant.database' => $company->database_name]);
        DB::purge('tenant');
        DB::reconnect('tenant');

        // 3. Authenticate in tenant DB
        $tenantUser = User::where('email', $request->email)->first();
        if (!$tenantUser || !Hash::check($request->password, $tenantUser->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        // 4. Generate token
        $token = $tenantUser->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $tenantUser,
            'token' => $token,
            'company' => $company->slug
        ]);
    }
}
