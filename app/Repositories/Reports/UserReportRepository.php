<?php

namespace App\Repositories\Reports;

use App\Repositories\Reports\BaseReportRepository;
use App\Models\User;
use Illuminate\Support\Collection;

class UserReportRepository extends BaseReportRepository
{
    public static function getData(array $filters): array
    {
        // Fetch all users ordered by ID
        $users = User::orderBy('id', 'asc')->get();

        // Return users dataset
        return ['users' => $users];
    }

    public static function transformToRows(array $data, array $filters): Collection
    {
        // Map users into report rows
        return collect($data['users'])->map(function ($user) {
            return [
                'ID' => $user->id, // User ID
                'User Type' => $user->userType->name,
                'Username' => $user->username, // Username
                'Name' => $user->name, // Full name
                'Email' => $user->email, // Email address
                'Created At' => $user->created_at, // Creation date
            ];
        });
    }
}
