<?php

namespace App\Http\Controllers\Api\V1\Auth; // Namespace for API v1 auth controllers

use App\Http\Controllers\Controller; // Base controller class
use Laravel\Sanctum\PersonalAccessToken; // Sanctum model for personal access tokens
use Illuminate\Http\Request; // HTTP request object

class LogoutController extends Controller // Logout controller class
{
  public function logout(Request $request) // Logout endpoint method
  {
    // Read the auth token value from the request cookies
    $cookie = $request->cookie('auth_token');

    // Find the Sanctum token record that matches the raw token string
    $token = PersonalAccessToken::findToken($cookie);

    // If a matching token record exists
    if ($token) {
      // Delete the token row from the database (invalidate session)
      $token->delete();
    }

    // Return a successful logout response
    return response()->json([
      'status' => 'success', // Response status
      'message' => 'Logout Successfully', // Response message
    ])
    // Remove the auth_token cookie from the client
    ->withCookie(cookie()->forget('auth_token'))
    // Remove the company_code cookie from the client
    ->withCookie(cookie()->forget('company_code'));
  }
}
