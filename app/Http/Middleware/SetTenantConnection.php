<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SetTenantConnection
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $tenants = Config::get('tenants.tenants');
        $tenant = null;
        foreach ($tenants as $t) {
            if (str_contains($host, $t['name'])) {
                $tenant = $t;
                break;
            }
        }
        // fallback: use header or param
        if (!$tenant) {
            $tenantName = $request->header('X-Tenant') ?? $request->get('tenant');
            foreach ($tenants as $t) {
                if ($t['name'] === $tenantName) {
                    $tenant = $t;
                    break;
                }
            }
        }
        if ($tenant) {
            Config::set('database.connections.tenant', [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => $tenant['db'],
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', 'Lenovo@123'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]);
            DB::setDefaultConnection('tenant');
            // Log tenant and database info for debugging
            info('Tenant resolved: ' . $tenant['name'] . ' | DB: ' . $tenant['db']);
        } else {
            info('Tenant not resolved for host: ' . $host . ' and header: ' . $request->header('X-Tenant'));
        }
        return $next($request);
    }
}
