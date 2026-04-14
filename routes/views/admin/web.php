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
use App\Features\Flashcards\Http\Controllers\Staff\StaffDashboardController;
use App\Features\Flashcards\Http\Controllers\Staff\SubadminController;
use App\Features\Flashcards\Http\Controllers\Staff\StaffDeckOverviewController;
use App\Features\Flashcards\Http\Controllers\Staff\StaffWordExportController;
use App\Features\Flashcards\Http\Middleware\EnsureAdminTenantView;
use App\Features\Flashcards\Http\Middleware\EnsureStaff;
use App\Features\Flashcards\Http\Middleware\EnsureSuperAdmin;

/*
|--------------------------------------------------------------------------
| Flashcards — admin view routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->name('social.redirect')
    ->where('provider', 'facebook|google');

Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->name('social.callback')
    ->where('provider', 'facebook|google');

Route::middleware('auth')->prefix('dashboard')->name('flashcards.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/words', [WordController::class, 'index'])->name('words.index');
    Route::get('/words/create', [WordController::class, 'create'])->name('words.create');
    Route::get('/words/bulk', [WordController::class, 'bulkCreate'])->name('words.bulk-create');
    Route::post('/words/bulk', [WordController::class, 'bulkQueue'])->name('words.bulk-queue');
    Route::get('/words/process-pending', [WordController::class, 'processPendingWords'])->name('words.process-pending');
    Route::get('/words/import', [WordController::class, 'import'])->name('words.import');
    Route::post('/words', [WordController::class, 'store'])->name('words.store');
    Route::get('/words/{hebrewForm}/edit', [WordController::class, 'edit'])->name('words.edit');
    Route::put('/words/{hebrewForm}', [WordController::class, 'update'])->name('words.update');
    Route::delete('/words/{hebrewForm}', [WordController::class, 'destroy'])->name('words.destroy');
    Route::post('/words/{hebrewForm}/add-to-deck', [WordController::class, 'addToDeck'])->name('words.add-to-deck');

    Route::get('/decks', [DeckController::class, 'index'])->name('decks.index');
    Route::get('/decks/{deck}', [DeckController::class, 'show'])->name('decks.show');
    Route::delete('/decks/{deck}/cards/{hebrewForm}', [DeckController::class, 'removeCard'])->name('decks.remove-card');

    Route::get('/learn', [LearnController::class, 'config'])->name('learn.config');
    Route::post('/learn/start', [LearnController::class, 'startSession'])->name('learn.start');
    Route::get('/learn/session', [LearnController::class, 'session'])->name('learn.session');
    Route::post('/learn/answer', [LearnController::class, 'submitAnswer'])->name('learn.answer');
    Route::post('/learn/reset', [LearnController::class, 'reset'])->name('progress.reset');
});

Route::middleware(['auth', EnsureAdminTenantView::class, EnsureStaff::class])
    ->prefix('dashboard/staff')
    ->name('flashcards.staff.')
    ->group(function () {
        Route::get('/', [StaffDashboardController::class, 'index'])->name('dashboard');
        Route::get('/decks', [StaffDeckOverviewController::class, 'index'])->name('decks.index');
        Route::get('/words/export.ndjson', [StaffWordExportController::class, 'json'])->name('words.export.ndjson');
    });

Route::middleware(['auth', EnsureAdminTenantView::class, EnsureSuperAdmin::class])
    ->prefix('dashboard/staff/subadmins')
    ->name('flashcards.staff.subadmins.')
    ->group(function () {
        Route::get('/', [SubadminController::class, 'index'])->name('index');
        Route::post('/', [SubadminController::class, 'store'])->name('store');
        Route::delete('/{user}', [SubadminController::class, 'destroy'])->name('destroy');
    });
