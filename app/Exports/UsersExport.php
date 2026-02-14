<?php
namespace App\Exports;
use App\Exports\Shared\DynamicExport;

class UsersExport extends DynamicExport
{
    public function __construct($users)
    {
        $exportData = [
            'file' => 'Users Report',
            'header' => ['ID','User Type', 'Username', 'Name', 'Email', 'Created At'],
            'data' => $users->map(function ($user) {
                return [
                    $user->id,
                    $user->userType->name ?? '',
                    $user->username ?? '',
                    $user->name ?? '',
                    $user->email ?? '',
                    $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : ''
                ];
            }),
            'columnWidths' => ['A' => 10, 'B' => 20,  'C' => 25, 'D' => 25, 'E' => 35, 'F' => 20]
        ];

        parent::__construct($exportData);
    }
}
