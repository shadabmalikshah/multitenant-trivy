<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Admin signup
    public function adminSignup(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'surname' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'date_of_birth' => 'required|date',
        ]);
        $tenantName = $request->header('X-Tenant');
        $tenants = config('tenants.tenants');
        $tenant = collect($tenants)->firstWhere('name', $tenantName);
        if (!$tenant) {
            return response()->json(['error' => 'Invalid tenant'], 422);
        }
        // Only allow one admin per tenant
        if (User::where('role', 'admin')->where('email', 'like', '%@admin.' . $tenantName . '.com')->exists()) {
            return response()->json(['error' => 'Admin already exists for this tenant'], 403);
        }
        $email = $request->email;
        if (!str_ends_with($email, '@admin.' . $tenantName . '.com')) {
            return response()->json(['error' => 'Admin email must be @admin.' . $tenantName . '.com'], 422);
        }
        $admin = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'date_of_birth' => $request->date_of_birth,
        ]);
    $token = JWTAuth::fromUser($admin);
    return response()->json(['message' => 'Admin registered', 'user' => $admin, 'token' => $token]);
    }
    // Admin login
    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $user = User::where('email', $request->email)->where('role', 'admin')->first();
        \Log::info('AuthController@adminLogin: Attempt login', ['email' => $request->email, 'user' => $user]);
        if (!$user || !Hash::check($request->password, $user->password)) {
            \Log::warning('AuthController@adminLogin: Invalid credentials', ['email' => $request->email]);
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials or not an admin.'],
            ]);
        }
    $token = JWTAuth::fromUser($user);
    \Log::info('AuthController@adminLogin: Login success', ['user' => $user, 'token' => $token]);
    return response()->json(['message' => 'Admin logged in', 'user' => $user, 'token' => $token]);
    }

    // User login
    public function userLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $user = User::where('email', $request->email)->where('role', 'user')->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials or not a user.'],
            ]);
        }
    $token = JWTAuth::fromUser($user);
    return response()->json(['message' => 'User logged in', 'user' => $user, 'token' => $token]);
    }

    // User signup
    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'surname' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'date_of_birth' => 'required|date',
        ]);
        // Validate email domain for tenant
        $email = $request->email;
        $tenantName = $request->header('X-Tenant');
        $tenants = config('tenants.tenants');
        $tenant = collect($tenants)->firstWhere('name', $tenantName);
        if (!$tenant || !str_ends_with($email, '@' . $tenant['name'] . '.com')) {
            throw ValidationException::withMessages([
                'email' => ['Email must match tenant domain.'],
            ]);
        }
        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'date_of_birth' => $request->date_of_birth,
        ]);
    $token = JWTAuth::fromUser($user);
    return response()->json(['message' => 'User registered', 'user' => $user, 'token' => $token]);
    }
}
