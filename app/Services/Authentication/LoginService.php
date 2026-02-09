<?php

namespace App\Services\Authentication;

use App\Interfaces\Authentication\LoginInterface;
use App\Helpers\{
    AuthHelper,
    CryptoHelper,
    DatabaseHelper,
    UserActivityHelper
};
use Illuminate\Support\Facades\Hash;

class LoginService
{
    protected $repository;

    public function __construct(LoginInterface $repository)
    {
        $this->repository = $repository;
    }

    public function login($validated, $ip, $userAgent)
    {
        $isGeneric = false;

        $company = $this->repository->validateCompanyCode($validated['company_code']);

        if (!$company) return AuthHelper::onError('company_not_found', $isGeneric);
        $raw = $company->fcon;


        $credential = [];
        $pairs = explode('|', $raw);
        foreach ($pairs as $pair) {
            if (strpos($pair, '=') !== false) {
                [$key, $value] = explode('=', $pair, 2);
                $credential[$key] = $value;
            }
        }

        DatabaseHelper::setConnection($credential);

        $user = $this->repository->authenticateUser($validated['username']);

        if (!$user) {
            UserActivityHelper::record([
                'user' => null,
                'event' => 'Login Failed',
                'auditable_type' => 'Authentication',
                'auditable_id' => 0,
                'old_value' => null,
                'new_value' => null,
                'remarks' => 'Login attempt failed due to incorrect credentials',
            ]);

            return AuthHelper::onError('user_not_found', $isGeneric);
        }

        if ($user->is_locked) {
            UserActivityHelper::record([
                'user' => $user,
                'event' => 'Account Locked',
                'auditable_type' => 'Authentication',
                'auditable_id' => 0,
                'old_value' => null,
                'new_value' => null,
                'remarks' => 'User attempted to login but account is locked',
            ]);

            return AuthHelper::onError('account_locked', $isGeneric);
        }

        $securityParameters = $this->repository->getSecurityParameters();

        if (!Hash::check($validated['password'], $user->password)) {
            $this->repository->incrementLoginAttempts($user->username);

            UserActivityHelper::record([
                'user' => $user,
                'event' => 'Login Failed',
                'auditable_type' => 'Authentication',
                'auditable_id' => 0,
                'old_value' => null,
                'new_value' => null,
                'remarks' => 'Login attempt failed due to incorrect credentials',
            ]);


            if ($securityParameters && $securityParameters->is_maximum_login_attempts_enabled == 1 && $securityParameters->maximum_login_attempts > 0) {
                $user->refresh();

                if ($user->login_attempts >= $securityParameters->maximum_login_attempts) {
                    UserActivityHelper::record([
                        'user' => $user,
                        'event' => 'Account Locked',
                        'auditable_type' => 'Authentication',
                        'auditable_id' => 0,
                        'old_value' => null,
                        'new_value' => null,
                        'remarks' => 'User account locked due to maximum login attempts exceeded',
                    ]);

                    $this->repository->lockAccount($user->username);

                    return AuthHelper::onError('account_locked', $isGeneric);
                }

                $remainingAttempts = $securityParameters->maximum_login_attempts - $user->login_attempts;

                return AuthHelper::onError('incorrect_credentials', $isGeneric, $remainingAttempts);
            }

            return AuthHelper::onError('incorrect_credentials', $isGeneric);
        }

        $this->repository->updateLoginInformation($user->username);

        $token = $user->createToken('auth');
        $token->accessToken->ip_address = $ip;
        $token->accessToken->user_agent = $userAgent;
        $token->accessToken->save();

        UserActivityHelper::record([
            'user' => $user,
            'event' => 'Login Success',
            'auditable_type' => 'Authentication',
            'auditable_id' => 0,
            'old_value' => null,
            'new_value' => null,
            'remarks' => 'Login successful',
        ]);

        return AuthHelper::onSuccess($token->plainTextToken, $validated['company_code']);
    }
}
