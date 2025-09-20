<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Movie;
use App\Models\Comment;
use Illuminate\Support\Facades\Hash;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_comment_and_view_comments()
    {
        $user = User::factory()->create([
            'email' => 'test@solar.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);
        $movie = Movie::factory()->create([
            'name' => 'Movie 1',
            'release_date' => '2025-01-01',
        ]);
        $this->actingAs($user);
        $response = $this->post("/movies/{$movie->id}/comments", [
            'comment' => 'Great movie!'
        ]);
        $response->assertStatus(200)->assertJson(['message' => 'Comment added']);

        $response = $this->get('/comments');
        $response->assertStatus(200)->assertJsonFragment(['comment' => 'Great movie!']);
    }

    public function test_admin_cannot_comment_or_view_comments()
    {
        $admin = User::factory()->create([
            'email' => 'shadab@solar.com',
            'password' => Hash::make('Cybage@123'),
            'role' => 'admin',
        ]);
        $movie = Movie::factory()->create([
            'name' => 'Movie 2',
            'release_date' => '2025-01-01',
        ]);
        $this->actingAs($admin);
        $response = $this->post("/movies/{$movie->id}/comments", [
            'comment' => 'Admin comment'
        ]);
        $response->assertStatus(403);
        $response = $this->get('/comments');
        $response->assertStatus(403);
    }
}
