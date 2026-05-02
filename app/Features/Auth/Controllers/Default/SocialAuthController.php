<?php

namespace App\Features\Auth\Controllers\Default;

use App\Features\Auth\Services\SocialAuthUserResolver;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController
{
    public function __construct(
        private readonly SocialAuthUserResolver $socialAuthUserResolver,
    ) {}

    public function redirect($provider)
    {
        if (!in_array($provider, ['facebook', 'google'], true)) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        try {
            if (!in_array($provider, ['facebook', 'google'], true)) {
                abort(404);
            }

            $socialUser = Socialite::driver($provider)->user();
            $user = $this->socialAuthUserResolver->findOrCreateUser($provider, $socialUser);

            Auth::login($user, true);

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            return redirect('/login')->withErrors([
                'social' => 'Authentication failed. Please try again.',
            ]);
        }
    }
}
