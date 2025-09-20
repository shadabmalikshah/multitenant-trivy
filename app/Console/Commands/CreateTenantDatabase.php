<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class CreateTenantDatabase extends Command
{
    protected $signature = 'tenant:create {tenant}';
    protected $description = 'Create database and run migrations for a tenant';

    public function handle()
    {
        $tenantName = $this->argument('tenant');
        $tenants = config('tenants.tenants');
        $tenant = collect($tenants)->firstWhere('name', $tenantName);
        if (!$tenant) {
            $this->error('Tenant not found in config.');
            return 1;
        }
        $dbName = $tenant['db'];
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', 'Lenovo@123');
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        // Create database
        try {
            $pdo = new \PDO("mysql:host=$host;port=$port", $username, $password);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $this->info("Database $dbName created or already exists.");
        } catch (\Exception $e) {
            $this->error('Error creating database: ' . $e->getMessage());
            return 1;
        }
        // Set config and run migrations
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => $host,
            'port' => $port,
            'database' => $dbName,
            'username' => $username,
            'password' => $password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);
        DB::setDefaultConnection('tenant');
        Artisan::call('migrate', ['--database' => 'tenant']);
        $this->info('Migrations run for tenant: ' . $tenantName);
        return 0;
    }
}
