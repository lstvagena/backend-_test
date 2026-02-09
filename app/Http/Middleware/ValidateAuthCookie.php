<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;
use App\Interfaces\Authentication\LoginInterface;

use App\Helpers\{
  AuthHelper,
  CryptoHelper,
  DatabaseHelper
};

use Exception;

class ValidateAuthCookie
{
  protected $loginRepository;

  public function __construct(LoginInterface $loginRepository)
  {
    $this->loginRepository = $loginRepository;
  }

  public function handle(Request $request, Closure $next): Response
  {
    if ($request->is('api/v1/auth/login')) {
      return $next($request);
    }

    $authToken = $request->cookie('auth_token');
    $companyCode = $request->cookie('company_code');

    if (!$authToken || !$companyCode) {
      return response()->json(AuthHelper::onError('authentication_required', false), 401);
    }

    $company = $this->loginRepository->validateCompanyCode($companyCode);

    if (!$company) {
      return response()->json(AuthHelper::onError('company_not_found', false), 401);
    }

    try {
      $raw = $company->fcon;

      // Parse pipe-delimited string: type=my|host=localhost|user=root|pass=Lstventures@123|dbname=abc_db
      $credential = [];
      $pairs = explode('|', $raw);
      foreach ($pairs as $pair) {
        if (strpos($pair, '=') !== false) {
          [$key, $value] = explode('=', $pair, 2);
          $credential[$key] = $value;
        }
      }
      DatabaseHelper::setConnection($credential);
      config(['sanctum.database_connection' => 'tenant']);
    } catch (Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to establish database connection. Please contact Lee Systems Technology Ventures, Inc. for assistance'
      ], 500);
    }

    $token = PersonalAccessToken::findToken($authToken);

    if (!$token) {
      return response()->json(AuthHelper::onError('authentication_required', false), 401);
    }

    if ($token->expires_at && now()->greaterThan($token->expires_at)) {
      return response()->json(AuthHelper::onError('authentication_required', false), 401);
    }

    return $next($request);
  }
}
