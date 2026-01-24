<?php

namespace App\Features\Auth\Controllers;

use App\Features\Auth\Models\User;
use App\Features\Auth\Models\SocialAccount;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SocialAuthController
{
    /**
     * Redirect to OAuth provider
     */
    public function redirect($provider)
    {
        // Validate provider
        if (!in_array($provider, ['facebook', 'google'])) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle OAuth callback
     */
    public function callback($provider)
    {
        try {
            // Validate provider
            if (!in_array($provider, ['facebook', 'google'])) {
                abort(404);
            }

            $socialUser = Socialite::driver($provider)->user();

            // Find or create user
            $user = $this->findOrCreateUser($provider, $socialUser);

            // Login user
            Auth::login($user, true);

            return redirect()->intended('/admin');
        } catch (\Exception $e) {
            return redirect('/login')->withErrors([
                'social' => 'Authentication failed. Please try again.',
            ]);
        }
    }

    /**
     * Find or create user from social provider
     */
    protected function findOrCreateUser($provider, $socialUser)
    {
        // Check if social account exists
        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($socialAccount) {
            return $socialAccount->user;
        }

        // Check if user with email exists
        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            // Create new user
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                'email' => $socialUser->getEmail(),
                'avatar' => $socialUser->getAvatar(),
                'password' => Hash::make(Str::random(32)), // Random password for OAuth users
                'email_verified_at' => now(),
            ]);
        }

        // Create or update social account link
        SocialAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ],
            [
                'access_token' => encrypt($socialUser->token ?? ''),
                'refresh_token' => $socialUser->refreshToken ? encrypt($socialUser->refreshToken) : null,
                'expires_at' => isset($socialUser->expiresIn) ? now()->addSeconds($socialUser->expiresIn) : null,
            ]
        );

        return $user;
    }
}
