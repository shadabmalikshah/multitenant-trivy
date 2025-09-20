<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantAdminSeeder extends Seeder
{
    public function run()
    {
        $tenants = Config::get('tenants.tenants');
        foreach ($tenants as $tenant) {
            // Switch DB connection to tenant
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

            // Check if admin user exists
            $exists = DB::table('users')->where('email', $tenant['admin_email'])->where('role', 'admin')->exists();
            if (!$exists) {
                DB::table('users')->insert([
                    'name' => 'Admin',
                    'surname' => ucfirst($tenant['name']),
                    'email' => $tenant['admin_email'],
                    'password' => Hash::make($tenant['admin_password']),
                    'role' => 'admin',
                    'date_of_birth' => '1990-01-01',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
