<?php
namespace App\Http\Middleware;

use Closure;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class CompanyDatabase
{
    public function handle($request, Closure $next)
    {
        $company = Company::where('slug', $request->route('company'))->firstOrFail();
        
        // Switch tenant DB
        config(['database.connections.tenant.database' => $company->database_name]);
        DB::purge('tenant');
        DB::reconnect('tenant');
        
        return $next($request); 
    }
}
