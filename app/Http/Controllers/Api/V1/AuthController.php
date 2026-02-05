<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\Api\V1\AuthResource;
use App\Services\TenantAuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly TenantAuthService $tenantAuthService
    ) {}

    /**
     * Authenticate user and return tenant token
     */
    public function login(LoginRequest $request): AuthResource
    {
        $result = $this->tenantAuthService->login($request->validated());
        return new AuthResource($result);
    }

    /**
     * Register user in central + tenant DBs
     */
    public function register(RegisterRequest $request): AuthResource
    {
        $result = $this->tenantAuthService->register($request->validated());
        return new AuthResource($result);
    }

    /**
     * Revoke current access token
     */
    public function logout(): JsonResponse
    {
        $this->tenantAuthService->logout();
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
