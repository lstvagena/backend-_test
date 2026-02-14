<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'username' => 'lstvsupervisor',
            'password' => Hash::make('Password'),
            'user_type_id' => 3, 
            'is_verified' => true,
        ]);

    }
}

