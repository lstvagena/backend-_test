<?php

namespace App\Http\Controllers\Api\V1\Utilities;

use App\Http\Controllers\Controller;
use App\Services\Utilities\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $service;

    // Injects UserService for business logic
    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    // Returns paginated users based on per_page query param
    /* 
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 3);  // Reads ?per_page= from query string, defaults to 3 if missing
        return response()->json(
            $this->service->fetchUsers($perPage) // Calls service to fetch paginated 
        );
    }*/

    public function index(Request $request)
    {
        return response()->json(
            $this->service->fetchUsers($request) // pass whole request to service
        );
    }

}
