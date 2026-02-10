<?php

namespace App\Services\Authentication;

use App\Repositories\Authentication\MeRepository;
use App\Models\General\Menu;
use App\Mappers\AuthMapper;

class MeService
{
  protected $repository;

  public function __construct(MeRepository $repository)
  {
    $this->repository = $repository;
  }

  public function getMe($token)
  {
    $user = $this->repository->getPersonalAccessToken($token)->tokenable; // Retrieves the personal access token record using the raw token,
    /* 
    $accessibleRoutes = [];
    if ($user->user_type !== 'Supervisor') {
      $userMenuCodes = $this->repository->getUserMenuCodes($user->user_id);
      $accessibleRoutes = Menu::select('path')
        ->whereIn('code', $userMenuCodes)
        ->whereNotNull('path')
        ->pluck('path')
        ->toArray();
    }*/
    return [
      'user' => AuthMapper::toMeResponse($user),
      //'accessible_routes' => $accessibleRoutes
    ];
  }
}
