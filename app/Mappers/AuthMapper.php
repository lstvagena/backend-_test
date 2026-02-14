<?php

namespace App\Mappers;

class AuthMapper
{
  public static function toMeResponse($user)
  {
    return [
      //'user_id'      => $user->user_id,
      'username'     => $user->username,
      'name'         => $user->name,
      //'middle_name'  => $user->middle_name,
      // 'last_name'    => $user->last_name,
      'email'        => $user->email,
      'user_type_id' => $user->user_type_id,
      // 'is_active'    => $user->is_active,
      'is_locked'    => $user->is_locked,
      'is_verified'    => $user->is_verified,
      // 'is_developer' => $user->is_developer,
      'last_login'   => $user->last_login,
      'created_at'   => $user->created_at,
    ];
  }
}
