<?php

namespace App\Helpers;
use Illuminate\Support\Facades\Cookie; // Cookie facade

class CookieHelper
{
  public static function setAuthCookie($token) // Create auth token cookie
  {
    return Cookie::make(
      'auth_token',                       // Cookie name
      $token,                            // Token value
      config('sanctum.expiration'),      // Expiration (minutes)
      '/',                               // Available for whole domain
      null,                              // Default domain
      config('app.env') == 'production', // Secure only in production (HTTPS)
      true,                              // HTTP only (JS can’t access)
      false,                             // Raw value (no URL encoding)
      'Lax'                              // SameSite policy
    );
  }

  public static function setCompanyCodeCookie($companyCode) // Company code cookie
  {
    return Cookie::make(
      'company_code',                    // Cookie name
      $companyCode,                     // Company identifier
      config('sanctum.expiration'),     // Expiration (minutes)
      '/',                              // Available for whole domain
      null,                             // Default domain
      config('app.env') == 'production',// Secure only in production
      true,                             // HTTP only
      false,                            // Not raw
      'Lax'                             // SameSite policy
    );
  }
}
