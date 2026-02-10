<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Services\Authentication\RegisterService;

class RegisterController extends Controller
{
    protected $service;
    // Holds the injected RegisterService instance

    public function __construct(RegisterService $service)
    // Constructor method with dependency injection of RegisterService
    {
        $this->service = $service;
        // Assigns the injected RegisterService to the class property
    }

    public function register(RegisterRequest $request)
    // Registration endpoint method that receives validated request data
    {
        $res = $this->service->register($request->validated());
        // Calls the register service with validated input data

        return response()->json([
            // Returns a JSON response
            'status'  => $res['status'],
            // Status of the registration process (success / error)

            'message' => $res['message'],
            // Human-readable message describing the result
        ], $res['status'] === 'success' ? 201 : 400);
        // Returns HTTP 201 Created on success, otherwise 400 Bad Request
    }
}
