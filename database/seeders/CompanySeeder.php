<?php
namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::create(['slug' => 'company1', 'database_name' => 'company1_db', 'name' => 'Company One']);
        Company::create(['slug' => 'company2', 'database_name' => 'company2_db', 'name' => 'Company Two']);
    }
}
