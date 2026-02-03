<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SetCompanyDatabase
{
    /**
     * Handle an incoming request.
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $company = $request->route('company');

        $databases = [
            'company1' => 'company1_db',
            'company2' => 'company2_db',
        ];

        if (!isset($databases[$company])) {
            return response()->json(['error' => 'Company not found'], 404);
        }

        // Switch tenant database dynamically
        Config::set('database.connections.tenant.database', $databases[$company]);
        DB::purge('tenant');
        DB::reconnect('tenant');

        return $next($request);
    }
}
