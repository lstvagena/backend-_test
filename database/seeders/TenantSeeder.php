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
        $companies = Company::on('mysql')->get();
        
        foreach ($companies as $company) {
            echo "Seeding {$company->name}...\n";
            
            // Switch tenant database
            Config::set('database.connections.tenant.database', $company->database_name);
            DB::purge('tenant');
            DB::reconnect('tenant');
            
            // UPDATE OR CREATE - Won't duplicate
            DB::connection('tenant')->table('users')->updateOrInsert(
                ['email' => 'admin@' . $company->slug . '.com'],  // Unique key
                [  // Data to insert/update
                    'name' => 'Admin - ' . $company->name,
                    'password' => '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
            
            echo "✅ Admin@{$company->slug}.com OK\n";
        }
    }
}
