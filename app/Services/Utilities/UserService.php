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
        // Get per-page value from query string (defaults to 3)
        $perPage = $request->get('per_page', 3);

        // Call repository method that runs the DB query
        $users = $this->repository->paginateUsers($perPage);

        // Return structured service response (no queries in return)
        return [
            'status' => 'success',
            'data'   => $users,
        ];
    }


    public function createUser($data)
    {
        $user = $this->repository->createUser($data);

        return [
            'status' => 'success',
            'data'   => $user
        ];
    }

    public function updateUser($id, $data)
    {
        $user = $this->repository->updateUser($id, $data);

        return [
            'status' => 'success',
            'data'   => $user
        ];
    }

}
