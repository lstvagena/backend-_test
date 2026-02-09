<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CompanyProgconf;

class CompanyProgconfSeeder extends Seeder
{
    public function run(): void
    {
        CompanyProgconf::create([
            'comcde' => 'ABC',
            'appcde' => 'HR',
            'fcon' => 'host=127.0.0.1|dbname=company1_db|user=root|pass=Lstventures@123',
        ]);
    }
}

