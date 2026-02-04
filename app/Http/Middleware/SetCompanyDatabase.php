<?php
namespace App\Http\Middleware;

use Closure;
use App\Models\Company;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SetCompanyDatabase
{
    public function handle($request, Closure $next)
    {
        // Get the "company" parameter from the route URL (e.g. /api/{company}/users)
        $companySlug = $request->route('company');
        
        // If no company slug is present in the route,
        // skip tenant switching and continue the request
        if (!$companySlug) {
            return $next($request);
        }
        
        // Find the company using the slug to get tenant database details
        // If not found, Laravel automatically returns a 404 error
        $company = Company::where('slug', $companySlug)->firstOrFail();
        
        // Dynamically set the tenant database name for this request
        Config::set('database.connections.tenant.database', $company->database_name);

        // Clear any existing tenant database connection from memory
        // This prevents using the wrong database
        DB::purge('tenant');

        // Reconnect to the tenant database using the updated configuration
        DB::reconnect('tenant');
        
        // Continue processing the request with the correct tenant database
        return $next($request);
    }

}
