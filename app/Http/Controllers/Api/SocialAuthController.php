<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function handleGoogle(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        // Verify the token with Google and get the user's info
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->userFromToken($request->access_token);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Invalid Google token.',
            ], 401);
        }

        // Case 1: This Google account has logged in before → just log them in
        $socialAccount = SocialAccount::where('provider', 'google')
            ->where('provider_id', $googleUser->getId())
            ->first();

        if ($socialAccount) {
            $user = $socialAccount->user;
            return $this->issueToken($user);
        }

        // Case 2: Email already exists from normal registration → link Google to it
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->socialAccounts()->create([
                'provider'    => 'google',
                'provider_id' => $googleUser->getId(),
            ]);
            return $this->issueToken($user);
        }

        // Case 3: Brand new user → create account then log them in
        $user = User::create([
            'full_name' => $googleUser->getName(),
            'email'     => $googleUser->getEmail(),
            'password'  => null,
            'role'      => 'customer',
        ]);

        $user->socialAccounts()->create([
            'provider'    => 'google',
            'provider_id' => $googleUser->getId(),
        ]);

        return $this->issueToken($user, 201);
    }

    // Shared helper — checks if account is active then returns the token
    private function issueToken(User $user, int $status = 200)
    {
        if (!$user->is_active) {
            return response()->json([
                'message' => 'Your account has been deactivated.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], $status);
    }
}