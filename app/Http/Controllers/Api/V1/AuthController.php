<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Services\TenantAuthService;

class AuthController extends Controller
{
    protected $tenantAuthService;

    public function __construct(TenantAuthService $tenantAuthService)
    {
        $this->tenantAuthService = $tenantAuthService;
    }

    public function login(LoginRequest $request)
    {
        $res = $this->tenantAuthService->login($request->validated());

        if ($res['status'] === 'success') {
            return response()->json($res, 200);
        }

        return response()->json($res, 401);
    }

    public function register(RegisterRequest $request)
    {
        $res = $this->tenantAuthService->register($request->validated());

        if ($res['status'] === 'success') {
            return response()->json($res, 201);
        }

        return response()->json($res, 400);
    }

    public function logout()
    {
        $res = $this->tenantAuthService->logout();
        return response()->json($res, 200);
    }
}
