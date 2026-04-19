<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'full_name'    => 'required|string|max:255',
            'email'        => 'required|email|unique:users',
            'phone_number' => 'required|string|max:20',
            'password'     => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'full_name'    => $data['full_name'],
            'email'        => $data['email'],
            'phone_number' => $data['phone_number'] ?? null,
            'password'     => Hash::make($data['password']),
            'role'         => 'customer',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        // User signed up with Google only — has no password
        if ($user && is_null($user->password)) {
            return response()->json([
                'message' => 'This account uses Google sign-in. Please log in with Google.',
            ], 422);
        }

        // Wrong email or wrong password
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        // Account is banned
        if (!$user->is_active) {
            return response()->json([
                'message' => 'Your account has been deactivated.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
