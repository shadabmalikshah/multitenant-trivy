<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SubdomainTenantMiddleware
{
    public function handle($request, Closure $next)
    {
        $host = $request->getHost();
        // e.g., solar.shubham.com or solar.localhost
        $parts = explode('.', $host);
        $tenant = $parts[0];
        $tenants = config('tenants.tenants');
        $tenantConfig = collect($tenants)->firstWhere('name', $tenant);
        if (!$tenantConfig) {
            return response()->json(['error' => 'Invalid tenant'], 404);
        }
        // Dynamically set DB connection for tenant
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $tenantConfig['db'],
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);
        // Set default connection for this request
        DB::setDefaultConnection('tenant');
        // Optionally, set tenant name in request for controllers
        $request->attributes->set('tenant', $tenant);
        return $next($request);
    }
}
