<?php

use Illuminate\Foundation\Inspiring;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

Artisan::command('tenants:setup', function () {
    $tenants = Config::get('tenants.tenants');
    foreach ($tenants as $tenant) {
        $dbName = $tenant['db'];
        // Create database if not exists
        DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

        // Set tenant connection
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $dbName,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', 'Lenovo@123'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);
        DB::setDefaultConnection('tenant');

        // Run migrations for tenant
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => '/database/migrations',
            '--force' => true,
        ]);
        // Seed admin user
        DB::connection('tenant')->table('users')->updateOrInsert(
            ['email' => $tenant['admin_email']],
            [
                'name' => 'Admin',
                'surname' => '',
                'email' => $tenant['admin_email'],
                'password' => Hash::make($tenant['admin_password']),
                'role' => 'admin',
                'date_of_birth' => null,
            ]
        );
        $this->info("Tenant {$tenant['name']} setup complete.");
    }
})->purpose('Create tenant databases and seed admin users');
