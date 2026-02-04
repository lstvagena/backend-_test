<?php
namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        
        foreach ($companies as $company) {
            Config::set('database.connections.tenant.database', $company->database_name);
            DB::purge('tenant');
            DB::reconnect('tenant');
            
            DB::connection('tenant')->table('users')->updateOrInsert(
                ['email' => 'admin@' . $company->slug . '.com'],
                [
                    'name' => 'Admin - ' . $company->name,
                    'password' => Hash::make('password'),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
}
