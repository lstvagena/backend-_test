<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Authentication\LoginService;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Helpers\CookieHelper;

class LoginController extends Controller
{
  protected $loginService;
  // Property to store the injected LoginService instance

  public function __construct(LoginService $loginService)
  // Constructor method with dependency injection of LoginService
  {
    $this->loginService = $loginService;
    // Assigns the injected LoginService to the class property
  }

  public function login(LoginRequest $request)
  // Login endpoint method that accepts a validated LoginRequest
  {
    $validated = $request->validated();
    // Retrieves validated input data (email, password, etc.)

    $ip = $request->ip();
    // Gets the client's IP address

    $userAgent = $request->userAgent();
    // Gets the client's browser / device user-agent string

    $res = $this->loginService->login($validated, $ip, $userAgent);
    // Calls the login service and passes credentials, IP, and user agent

    if ($res['status'] === 'success' && isset($res['token'], $res['company'])) {
      // Checks if login was successful and required data exists

      return response()->json([
        // Returns a JSON response
        'status'       => $res['status'],
        // Login status (success)

        'message'      => $res['message'],
        // Human-readable login message

        'token'        => $res['token'],
        // Authentication token (Sanctum / JWT / custom)

        'company_code' => $res['company'],
        // Company code associated with the authenticated user
      ], 200)
      // HTTP 200 OK response

      ->withCookie(CookieHelper::setAuthCookie($res['token']))
      // Attaches authentication cookie to the response

      ->withCookie(CookieHelper::setCompanyCodeCookie($res['company']));
      // Attaches company code cookie to the response
    }

    return response()->json($res, 401);
    // Returns error response with HTTP 401 Unauthorized if login fails
  }
}
