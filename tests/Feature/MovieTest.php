<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Movie;
use Illuminate\Support\Facades\Hash;

class MovieTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_delete_movie()
    {
        $admin = User::factory()->create([
            'email' => 'shadab@solar.com',
            'password' => Hash::make('Cybage@123'),
            'role' => 'admin',
        ]);
        $this->actingAs($admin);
        $response = $this->post('/movies', [
            'name' => 'Movie 1',
            'image' => 'image.jpg',
            'description' => 'Desc',
            'release_date' => '2025-01-01',
        ]);
        $response->assertStatus(200)->assertJson(['message' => 'Movie created']);

        $movieId = $response->json('movie.id');
        $response = $this->put("/movies/$movieId", [
            'name' => 'Movie 1 Updated',
        ]);
        $response->assertStatus(200)->assertJson(['message' => 'Movie updated']);

        $response = $this->delete("/movies/$movieId");
        $response->assertStatus(200)->assertJson(['message' => 'Movie deleted']);
    }

    public function test_user_cannot_create_update_delete_movie()
    {
        $user = User::factory()->create([
            'email' => 'test@solar.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);
        $this->actingAs($user);
        $response = $this->post('/movies', [
            'name' => 'Movie 2',
            'release_date' => '2025-01-01',
        ]);
        $response->assertStatus(403);
    }
}
