<?php

namespace App\Features\Auth\Controllers;

use App\Helpers\TenancyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return TenancyHelper::view('login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($request->only('email', 'password'), $remember)) {
            $request->session()->regenerate();

            return redirect()->intended('/admin');
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }
}
