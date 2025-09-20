<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_success()
    {
        $admin = User::factory()->create([
            'email' => 'shadab@solar.com',
            'password' => Hash::make('Cybage@123'),
            'role' => 'admin',
        ]);
        $response = $this->post('/admin/login', [
            'email' => 'shadab@solar.com',
            'password' => 'Cybage@123',
        ]);
        $response->assertStatus(200)->assertJson(['message' => 'Admin logged in']);
    }

    public function test_user_signup_and_login_success()
    {
        $response = $this->post('/user/signup', [
            'name' => 'Test',
            'surname' => 'User',
            'email' => 'test@solar.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'date_of_birth' => '2000-01-01',
        ]);
        $response->assertStatus(200)->assertJson(['message' => 'User registered']);

        $response = $this->post('/user/login', [
            'email' => 'test@solar.com',
            'password' => 'password123',
        ]);
        $response->assertStatus(200)->assertJson(['message' => 'User logged in']);
    }

    public function test_signup_invalid_email_domain()
    {
        $response = $this->post('/user/signup', [
            'name' => 'Test',
            'surname' => 'User',
            'email' => 'test@invalid.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'date_of_birth' => '2000-01-01',
        ]);
        $response->assertStatus(422);
    }
}
