<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
  public function logout(Request $request)
  {
    $cookie = $request->cookie('auth_token');

    $token = PersonalAccessToken::findToken($cookie);

    if ($token) {
      $token->delete();
    }

    return response()->json([
      'status' => 'success',
      'message' => 'Logout Successfully',
    ])->withCookie(cookie()->forget('auth_token'))
      ->withCookie(cookie()->forget('company_code'));
  }
}
