<?php

namespace App\Http\Middleware;

use Closure; // Used to pass the request to the next middleware/controller
use Illuminate\Http\Request; // Represents the incoming HTTP request
use Symfony\Component\HttpFoundation\Response; // Standard HTTP response type
use Laravel\Sanctum\PersonalAccessToken; // Sanctum model for API tokens
use App\Interfaces\Authentication\LoginInterface; // Interface for login-related DB actions

use App\Helpers\{
  AuthHelper,      // Helper for standardized auth responses
  CryptoHelper,    // Helper for encrypting/decrypting DB credentials
  DatabaseHelper  // Helper for dynamically switching database connections
};

use Exception; // Used to catch runtime errors

class ValidateAuthCookie
{
  protected $loginRepository; // Repository instance for auth-related queries

  public function __construct(LoginInterface $loginRepository)
  {
    $this->loginRepository = $loginRepository; // Inject the repository via Laravel container
  }

  public function handle(Request $request, Closure $next): Response
  {
    // Skip authentication and DB switching for login and logout routes
    if ($request->is('api/v1/auth/login')) {
      return $next($request); // Immediately continue to controller
    }

    // Retrieve auth token from cookies
    $authToken = $request->cookie('auth_token');

    // Retrieve company code from cookies
    $companyCode = $request->cookie('company_code');

    // If either token or company code is missing, user is not authenticated
    if (!$authToken || !$companyCode) {
      return response()->json(
        AuthHelper::onError('authentication_required', false),
        401
      );
    }

    // Validate that the company code exists in the central configuration table
    $company = $this->loginRepository->validateCompanyCode($companyCode);

    // If company configuration is not found, reject the request
    if (!$company) {
      return response()->json(
        AuthHelper::onError('company_not_found', false),
        401
      );
    }

    try {
      // Parse pipe-delimited string: type=my|host=localhost|user=root|pass=Lstventures@123|dbname=abc_db
      $credential = []; // Initialize an empty array to hold parsed database credentials

      $fcon = $company->fcon; // <----get the valie of fcon, i make it durect no encryption, decryption

      $pairs = explode('|', $fcon); 

      foreach ($pairs as $pair) { // Loop through each key=value segment from the connection string

        if (strpos($pair, '=') !== false) { // Ensure the segment contains a valid key=value format

          [$key, $value] = explode('=', $pair, 2); // Split the segment into key and value (limit 2 prevents extra '=' issues)

          $credential[$key] = $value; // Store the parsed credential in the array using the key name
        }
      }

      DatabaseHelper::setConnection($credential); // Dynamically switch the database connection using parsed credentials

    } catch (Exception $e) { // Catch any error from parsing or database connection

      // If decryption or DB switching fails, return a server error
      return response()->json([
        'status'  => 'error',
        'message' => 'Failed to establish database connection. Please contact Lee Systems Technology Ventures, Inc. for assistance'
      ], 500);
    }

    // Look up the Sanctum token using the raw token value
    $token = PersonalAccessToken::findToken($authToken);

    // If token does not exist, authentication fails
    if (!$token) {
      return response()->json(
        AuthHelper::onError('authentication_required', false),
        401
      );
    }

    // If token has an expiration date and is already expired, reject it
    if ($token->expires_at && now()->greaterThan($token->expires_at)) {
      return response()->json(
        AuthHelper::onError('authentication_required', false),
        401
      );
    }

    // All checks passed — allow request to proceed to the next middleware/controller
    return $next($request);
  }
}
