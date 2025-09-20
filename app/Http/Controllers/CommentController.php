<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Movie;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // Add a comment to a movie (user only)
    public function store(Request $request, $movieId)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'user') {
            abort(403, 'Only users can comment');
        }
        $userId = $user->id;
        $request->validate([
            'comment' => 'required|string',
        ]);
        $movie = Movie::findOrFail($movieId);
        $comment = Comment::create([
            'movie_id' => $movie->id,
            'user_id' => $userId,
            'comment' => $request->comment,
        ]);
        return response()->json(['message' => 'Comment added', 'comment' => $comment]);
    }

    // View all comments for all movies (user only)
    public function index()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'user') {
            abort(403, 'Only users can view comments');
        }
        $comments = Comment::with('movie')
            ->where('user_id', $user->id)
            ->get();
        return response()->json($comments);
    }

    // View all comments for a particular movie (with user names)
    public function movieComments($movieId)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'user') {
            abort(403, 'Only users can view comments');
        }
        $comments = Comment::with('user')
            ->where('movie_id', $movieId)
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'user_name' => $comment->user ? $comment->user->name : null,
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at,
                ];
            });
        return response()->json($comments);
    }
}
