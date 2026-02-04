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
use Illuminate\Support\Facades\Config;

class AuthController extends Controller
{
    // Register a new user in both central and tenant databases
    public function register(Request $request)
    {
        // Validate required fields before processing registration
        $request->validate([
            'name' => 'required|string|max:255',              // User full name
            'email' => 'required|email|unique:central_users', // Must be unique in central DB
            'password' => 'required|min:8',                   // Minimum 8 characters
            'company_slug' => 'required|exists:companies,slug', // Identifies tenant company
        ]);

        // Create user record in main database (maps user to company)
        $centralUser = CentralUser::create([
            'email' => $request->email,
            'company_slug' => $request->company_slug,
        ]);

        // Fetch company to get tenant database name
        $company = Company::where('slug', $request->company_slug)->firstOrFail();

        // Dynamically set the tenant database connection
        Config::set('database.connections.tenant.database', $company->database_name);

        // Remove any previously cached tenant connection
        DB::purge('tenant');

        // Reconnect using the newly set tenant database
        DB::reconnect('tenant');

        // Create the user inside the tenant database
        $tenantUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Store hashed password
        ]);

        // Link central user record to tenant user ID
        $centralUser->update([
            'tenant_user_id' => $tenantUser->id
        ]);

        // Generate API authentication token for the user
        $token = $tenantUser->createToken('api-token')->plainTextToken;

        // Return successful registration response
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $tenantUser,
            'token' => $token,
            'company' => $company->slug
        ], 201);
    }


    // Login user
    public function login(Request $request)
    {
        // Validate required login credentials
        $request->validate([
            'email' => 'required|email',    // User email address
            'password' => 'required',       // User password
        ]);

        // Look up the user in the central database by email
        $centralUser = CentralUser::where('email', $request->email)->first();

        // Stop login if the user does not exist in central database
        if (!$centralUser) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        // Retrieve the company to determine the tenant database
        $company = Company::where('slug', $centralUser->company_slug)->firstOrFail();

        // Dynamically switch to the user's tenant database
        Config::set('database.connections.tenant.database', $company->database_name);

        // Clear previous tenant connection to avoid wrong database usage
        DB::purge('tenant');

        // Reconnect using the selected tenant database
        DB::reconnect('tenant');

        // Find the user record inside the tenant database
        $tenantUser = User::where('email', $request->email)->first();

        // Verify user exists and password matches the stored hash
        if (!$tenantUser || !Hash::check($request->password, $tenantUser->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        // Create a new API token for authenticated requests
        $token = $tenantUser->createToken('api-token')->plainTextToken;

        // Return authenticated user data and token
        return response()->json([
            'user' => $tenantUser,
            'token' => $token,
            'company' => $company->slug
        ]);
    }

}

