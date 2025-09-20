<?php

use Illuminate\Support\Facades\Route;


// All API routes use SetTenantConnection middleware
Route::middleware(\App\Http\Middleware\SetTenantConnection::class)->group(function () {
	// Public routes
	Route::post('/user/login', [\App\Http\Controllers\AuthController::class, 'userLogin']);
	Route::post('/user/signup', [\App\Http\Controllers\AuthController::class, 'signup']);

	// Admin signup (only one allowed, email domain constraint)
	Route::post('/admin/signup', [\App\Http\Controllers\AuthController::class, 'adminSignup']);
	Route::post('/admin/login', [\App\Http\Controllers\AuthController::class, 'adminLogin']);

	// Protected routes (require API token)
	Route::middleware(\App\Http\Middleware\ApiAuthenticate::class)->group(function () {
		// Movie CRUD routes
		Route::get('/movies', [\App\Http\Controllers\MovieController::class, 'index']);
		Route::post('/movies', [\App\Http\Controllers\MovieController::class, 'store']);
		Route::put('/movies/{id}', [\App\Http\Controllers\MovieController::class, 'update']);
		Route::delete('/movies/{id}', [\App\Http\Controllers\MovieController::class, 'destroy']);

		// Movie comment routes
		Route::get('/comments', [\App\Http\Controllers\CommentController::class, 'index']);
		Route::post('/movies/{movieId}/comments', [\App\Http\Controllers\CommentController::class, 'store']);
		Route::get('/movies/{movieId}/all-comments', [\App\Http\Controllers\CommentController::class, 'movieComments']);
	});
});
