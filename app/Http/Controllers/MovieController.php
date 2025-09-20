<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MovieController extends Controller
{
    // Create a movie (admin only)
    public function store(Request $request)
    {
        $user = Auth::user();
        \Log::info('MovieController@store: Auth user', ['user' => $user]);
        if (!$user || $user->role !== 'admin') {
            \Log::warning('MovieController@store: Unauthorized attempt to create movie', ['user' => $user]);
            abort(403, 'Only admin can create movies');
        }
        $request->validate([
            'name' => 'required|string',
            'image' => 'nullable|string', // For now, just path or URL
            'description' => 'nullable|string',
            'release_date' => 'required|date',
        ]);
        $movie = Movie::create($request->only(['name', 'image', 'description', 'release_date']));
        return response()->json(['message' => 'Movie created', 'movie' => $movie]);
    }

    // Read all movies
    public function index()
    {
    $user = Auth::user();
    \Log::info('MovieController@index: Auth user', ['user' => $user]);
    $movies = Movie::all();
    return response()->json($movies);
    }

    // Update a movie (admin only)
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Only admin can update movies');
        }
        $movie = Movie::findOrFail($id);
        $request->validate([
            'name' => 'sometimes|string',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
            'release_date' => 'sometimes|date',
        ]);
        $movie->update($request->only(['name', 'image', 'description', 'release_date']));
        return response()->json(['message' => 'Movie updated', 'movie' => $movie]);
    }

    // Delete a movie (admin only)
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Only admin can delete movies');
        }
        $movie = Movie::findOrFail($id);
        $movie->delete();
        return response()->json(['message' => 'Movie deleted']);
    }
}
