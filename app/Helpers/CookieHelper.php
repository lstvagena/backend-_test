<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cookie;

class CookieHelper
{
  public static function setAuthCookie($token)
  {
    return Cookie::make(
      'auth_token',
      $token,
      config('sanctum.expiration'),
      '/',
      null,
      config('app.env') == 'production',
      true,
      false,
      'Lax'
    );
  }

  public static function setCompanyCodeCookie($companyCode)
  {
    return Cookie::make(
      'company_code',
      $companyCode,
      config('sanctum.expiration'),
      '/',
      null,
      config('app.env') == 'production',
      true,
      false,
      'Lax'
    );
  }
}
