<?php

use Illuminate\Support\Facades\Route;
use App\Features\Auth\Controllers\LoginController;
use App\Features\Auth\Controllers\LogoutController;
use App\Features\Auth\Controllers\RegisterController;
use App\Features\Auth\Controllers\SocialAuthController;

/*
|--------------------------------------------------------------------------
| Flashcards Tenant Routes
|--------------------------------------------------------------------------
|
| These routes are automatically loaded by Larabis when the flashcards
| tenant is active. They handle user authentication including:
| - Email/password login and registration
| - Social authentication (Facebook, Google)
| - Logout functionality
|
*/

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// Registration routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Social authentication routes
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->name('social.redirect')
    ->where('provider', 'facebook|google');

Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->name('social.callback')
    ->where('provider', 'facebook|google');
