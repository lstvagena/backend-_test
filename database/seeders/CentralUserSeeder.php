<?php
namespace Database\Seeders;

use App\Models\CentralUser;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CentralUserSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        foreach ($companies as $company) {
            CentralUser::create([
                'email' => 'admin@' . $company->slug . '.com',
                'company_slug' => $company->slug,
            ]);
        }
    }
}
