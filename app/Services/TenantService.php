<?php
namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantService
{
    protected $tenant;

    public function resolveTenant($request)
    {
        // For local: resolve by header, param, or subdomain
        $host = $request->getHost();
        $tenants = Config::get('tenants.tenants');
        foreach ($tenants as $tenant) {
            if (str_contains($host, $tenant['name'])) {
                $this->tenant = $tenant;
                return $tenant;
            }
        }
        // fallback: use header or param
        $tenantName = $request->header('X-Tenant') ?? $request->get('tenant');
        foreach ($tenants as $tenant) {
            if ($tenant['name'] === $tenantName) {
                $this->tenant = $tenant;
                return $tenant;
            }
        }
        return null;
    }

    public function setTenantConnection()
    {
        if (!$this->tenant) return;
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $this->tenant['db'],
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', 'Lenovo@123'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);
        DB::setDefaultConnection('tenant');
    }

    public function getTenant()
    {
        return $this->tenant;
    }
}
