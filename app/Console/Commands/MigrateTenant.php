<?php
namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigrateTenant extends Command
{
    protected $signature = 'tenant:migrate {company?}';
    protected $description = 'Migrate tenant databases';

    public function handle()
    {
        $companies = $this->argument('company')
            ? [Company::where('slug', $this->argument('company'))->firstOrFail()]
            : Company::all();

        foreach ($companies as $company) {
            $this->info("Fresh migrating {$company->database_name}...");
            
            Config::set('database.connections.tenant.database', $company->database_name);
            DB::purge('tenant');
            DB::reconnect('tenant');
            
            // DROPS ALL TABLES + Recreates fresh
            $this->call('migrate:fresh', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true
            ]);
            
            $this->info("✅ {$company->database_name} FRESH migrated!");
        }
    }

}
