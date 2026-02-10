<?php

namespace App\Services\Utilities;
use Illuminate\Http\Request;
use App\Interfaces\Utilities\UserInterface;

class UserService
{
    protected $repository;

    // Injects the UserRepository for database access
    public function __construct(UserInterface $repository)
    {
        $this->repository = $repository;
    }

    // Returns paginated users in a standard API response format
    public function fetchUsers(Request $request)
    {
        $perPage = $request->get('per_page', 3); // service now controls pagination logic

        return [
            'status' => 'success',
            'data'   => $this->repository->paginateUsers($perPage),
        ];
    }

}
