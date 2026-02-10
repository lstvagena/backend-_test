<?php

namespace App\Services\Authentication; 
// Defines the namespace for the LoginService (keeps code organized)

use App\Interfaces\Authentication\LoginInterface;
// Interface that this service depends on (repository pattern)

use App\Helpers\{
    AuthHelper,        // Handles standardized auth success/error responses
    CryptoHelper,      // (Imported but NOT used in this file – can be removed)
    DatabaseHelper,    // Handles dynamic database switching (multi-tenant)
    UserActivityHelper // Logs user activity (login success/failure, locks, etc.)
};

use Illuminate\Support\Facades\Hash;
// Laravel facade for securely hashing and verifying passwords

class LoginService
{
    protected $repository;
    // Holds the injected repository that implements LoginInterface

    public function __construct(LoginInterface $repository)
    {
        $this->repository = $repository;
        // Dependency Injection: assign repository to this service
    }

    public function login($validated, $ip, $userAgent)
    {
        // Main login function
        // $validated  → validated request data (username, password, company_code)
        // $ip         → client IP address
        // $userAgent  → client browser/device info

        $isGeneric = false;
        // Flag used to determine if error messages should be generic or specific

        $company = $this->repository->validateCompanyCode($validated['company_code']);
        // Validate if the provided company code exists

        if (!$company)
            return AuthHelper::onError('company_not_found', $isGeneric);
        // Stop login immediately if company does not exist

        $fcon = $company->fcon;
        // Get the company database connection string (e.g. host=...|db=...)

        $credential = [];
        // Will store parsed database credentials as key-value pairs

        $pairs = explode('|', $fcon);
        // Split the connection string into parts using "|" delimiter

        foreach ($pairs as $pair) {
            // Loop through each key=value pair

            if (strpos($pair, '=') !== false) {
                // Ensure the pair actually contains "="

                [$key, $value] = explode('=', $pair, 2);
                // Split into key and value (limit 2 prevents over-splitting)

                $credential[$key] = $value;
                // Store in credentials array
            }
        }

        DatabaseHelper::setConnection($credential);
        // Dynamically switch the database connection for this request

        $user = $this->repository->authenticateUser($validated['username']);
        // Retrieve user record from the company database

        if (!$user) {
            // If user does not exist

            UserActivityHelper::record([
                'user' => null, // No user object available
                'event' => 'Login Failed',
                'auditable_type' => 'Authentication',
                'auditable_id' => 0,
                'old_value' => null,
                'new_value' => null,
                'remarks' => 'Login attempt failed due to incorrect credentials',
            ]);
            // Log failed login attempt

            return AuthHelper::onError('user_not_found', $isGeneric);
            // Return standardized error response
        }

        if ($user->is_locked) {
            // Check if user account is already locked

            UserActivityHelper::record([
                'user' => $user,
                'event' => 'Account Locked',
                'auditable_type' => 'Authentication',
                'auditable_id' => 0,
                'old_value' => null,
                'new_value' => null,
                'remarks' => 'User attempted to login but account is locked',
            ]);
            // Log locked account login attempt

            return AuthHelper::onError('account_locked', $isGeneric);
            // Stop login if account is locked
        }

        $securityParameters = $this->repository->getSecurityParameters();
        // Fetch security settings (max login attempts, lock rules, etc.)

        if (!Hash::check($validated['password'], $user->password)) {
            // Verify password against hashed password in DB

            $this->repository->incrementLoginAttempts($user->username);
            // Increment failed login attempts counter

            UserActivityHelper::record([
                'user' => $user,
                'event' => 'Login Failed',
                'auditable_type' => 'Authentication',
                'auditable_id' => 0,
                'old_value' => null,
                'new_value' => null,
                'remarks' => 'Login attempt failed due to incorrect credentials',
            ]);
            // Log failed login attempt

            if (
                $securityParameters &&
                $securityParameters->is_maximum_login_attempts_enabled == 1 &&
                $securityParameters->maximum_login_attempts > 0
            ) {
                // Check if login attempt limits are enabled

                $user->refresh();
                // Reload user data from DB to get updated attempts count

                if ($user->login_attempts >= $securityParameters->maximum_login_attempts) {
                    // If user exceeded allowed login attempts

                    UserActivityHelper::record([
                        'user' => $user,
                        'event' => 'Account Locked',
                        'auditable_type' => 'Authentication',
                        'auditable_id' => 0,
                        'old_value' => null,
                        'new_value' => null,
                        'remarks' => 'User account locked due to maximum login attempts exceeded',
                    ]);
                    // Log account lock event

                    $this->repository->lockAccount($user->username);
                    // Lock the user account in database

                    return AuthHelper::onError('account_locked', $isGeneric);
                    // Return locked account response
                }

                $remainingAttempts =
                    $securityParameters->maximum_login_attempts - $user->login_attempts;
                // Calculate remaining login attempts

                return AuthHelper::onError(
                    'incorrect_credentials',
                    $isGeneric,
                    $remainingAttempts
                );
                // Return error with remaining attempts info
            }

            return AuthHelper::onError('incorrect_credentials', $isGeneric);
            // Return generic incorrect credentials error
        }

        $this->repository->updateLoginInformation($user->username);
        // Reset login attempts and update last login details

        $token = $user->createToken('auth');
        // Create a new Sanctum personal access token

        $token->accessToken->ip_address = $ip;
        // Store client IP in token record

        $token->accessToken->user_agent = $userAgent;
        // Store user agent (browser/device)

        $token->accessToken->save();
        // Persist token metadata to database

        UserActivityHelper::record([
            'user' => $user,
            'event' => 'Login Success',
            'auditable_type' => 'Authentication',
            'auditable_id' => 0,
            'old_value' => null,
            'new_value' => null,
            'remarks' => 'Login successful',
        ]);
        // Log successful login

        return AuthHelper::onSuccess(
            $token->plainTextToken,
            $validated['company_code']
        );
        // Return success response with token and company code
    }
}
