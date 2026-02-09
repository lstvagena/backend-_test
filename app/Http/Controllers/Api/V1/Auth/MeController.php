<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Authentication\MeService;
use Illuminate\Http\Request;

class MeController extends Controller
{
  protected $service;

  public function __construct(MeService $service)
  {
    $this->service = $service;
  }

  public function me(Request $request)
  {
    $token = $request->cookie('auth_token');
    return response()->json($this->service->getMe($token));
  }
}
