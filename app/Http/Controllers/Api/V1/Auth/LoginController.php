<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Authentication\LoginService;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Helpers\CookieHelper;

class LoginController extends Controller
{
  protected $loginService;

  public function __construct(LoginService $loginService)
  {
    $this->loginService = $loginService;
  }

  public function login(LoginRequest $request)
  {
    $validated = $request->validated();
    $ip = $request->ip();
    $userAgent = $request->userAgent();

    $res = $this->loginService->login($validated, $ip, $userAgent);

    if ($res['status'] == 'success' && isset($res['token']) && isset($res['company_code'])) {
      return response()->json([
        'status' => $res['status'],
        'message' => $res['message'],
      ], 200)->withCookie(CookieHelper::setAuthCookie($res['token']))
        ->withCookie(CookieHelper::setCompanyCodeCookie($res['company_code']));
    }

    return response()->json($res, 401);
  }
}
