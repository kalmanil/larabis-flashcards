<?php

use Illuminate\Support\Facades\Route;
use App\Features\Auth\Controllers\LoginController;
use App\Features\Auth\Controllers\LogoutController;
use App\Features\Auth\Controllers\RegisterController;
use App\Features\Auth\Controllers\SocialAuthController;
use App\Features\Flashcards\Http\Controllers\DashboardController;
use App\Features\Flashcards\Http\Controllers\WordController;
use App\Features\Flashcards\Http\Controllers\DeckController;
use App\Features\Flashcards\Http\Controllers\LearnController;

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
| - Flashcards (words, decks, learning)
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

// Flashcards (auth required)
Route::middleware('auth')->prefix('admin')->name('flashcards.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Words (tenant pool)
    Route::get('/words', [WordController::class, 'index'])->name('words.index');
    Route::get('/words/create', [WordController::class, 'create'])->name('words.create');
    Route::post('/words', [WordController::class, 'store'])->name('words.store');
    Route::get('/words/{hebrewForm}/edit', [WordController::class, 'edit'])->name('words.edit');
    Route::put('/words/{hebrewForm}', [WordController::class, 'update'])->name('words.update');
    Route::delete('/words/{hebrewForm}', [WordController::class, 'destroy'])->name('words.destroy');
    Route::post('/words/{hebrewForm}/add-to-deck', [WordController::class, 'addToDeck'])->name('words.add-to-deck');

    // Decks (my cards)
    Route::get('/decks', [DeckController::class, 'index'])->name('decks.index');
    Route::get('/decks/{deck}', [DeckController::class, 'show'])->name('decks.show');
    Route::delete('/decks/{deck}/cards/{hebrewForm}', [DeckController::class, 'removeCard'])->name('decks.remove-card');

    // Learning
    Route::get('/learn', [LearnController::class, 'config'])->name('learn.config');
    Route::post('/learn/start', [LearnController::class, 'startSession'])->name('learn.start');
    Route::get('/learn/session', [LearnController::class, 'session'])->name('learn.session');
    Route::post('/learn/answer', [LearnController::class, 'submitAnswer'])->name('learn.answer');
    Route::post('/learn/reset', [LearnController::class, 'reset'])->name('progress.reset');
});
