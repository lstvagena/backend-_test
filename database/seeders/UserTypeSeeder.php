<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserType;

class UserTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Super Admin',
            'Admin',
            'HR',
            'Supervisor',
            'Employee',
        ];

        foreach ($types as $type) {
            UserType::create([
                'name' => $type,
            ]);
        }
    }
}
