<?php

namespace App\Repositories\Reports;

use App\Repositories\Reports\BaseReportRepository;
use App\Models\User;
use Illuminate\Support\Collection;

class UserReportRepository extends BaseReportRepository
{
    public static function getData(array $filters): array
    {
        $users = User::orderBy('id', 'asc')->get();

        return ['users' => $users];
    }

    public static function transformToRows(array $data, array $filters): Collection
    {
        return collect($data['users'])->map(function ($user) {
            return [
                'ID' => $user->id,
                'Username' => $user->username,
                'Name' => $user->name,
                'Email' => $user->email,
                'Created At' => $user->created_at,
            ];
        });
    }
}
