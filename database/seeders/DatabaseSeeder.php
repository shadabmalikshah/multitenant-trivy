<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Example: User::factory(10)->create();
        // Example: $this->call(UserSeeder::class);
        // Example: $this->call(MovieSeeder::class);
        $this->call(TenantAdminSeeder::class);
    }
}
