<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Utilities\BaselineSecurity\SecurityParameter;

class SecurityParameterSeeder extends Seeder
{
    public function run(): void
    {
        SecurityParameter::create([
            'is_minimum_password_length_enabled' => 1,
            'minimum_password_length' => 8,
            'is_maximum_password_length_enabled' => 1,
            'maximum_password_length' => 12,
            'is_minimum_lowercase_required_enabled' => 1,
            'minimum_lowercase_required' => 1,
            'is_minimum_uppercase_required_enabled' => 1,
            'minimum_uppercase_required' => 2,
            'is_minimum_numeric_required_enabled' => 1,
            'minimum_numeric_required' => 1,
            'is_minimum_special_char_required_enabled' => 1,
            'minimum_special_char_required' => 3,
            'is_maximum_login_attempts_enabled' => 1,
            'maximum_login_attempts' => 5,
            'is_restrict_username_as_password' => 1,
            'is_restrict_sequential_char_as_password' => 1,
            'is_restrict_repetitive_char_as_password' => 1,
            'is_force_login_allowed' => 1,
        ]);
    }
}

