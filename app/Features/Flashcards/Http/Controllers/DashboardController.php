<?php

namespace App\Features\Flashcards\Http\Controllers;

use App\Features\Flashcards\Models\HebrewForm;
use App\Helpers\TenancyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $defaultDeck = $user->decks()->where('is_default', true)->first();
        $cardCount = $defaultDeck ? $defaultDeck->deckCards()->count() : 0;
        $pendingEnrichmentCount = HebrewForm::pendingEnrichment()->count();

        return TenancyHelper::view('flashcards.dashboard', [
            'cardCount' => $cardCount,
            'pendingEnrichmentCount' => $pendingEnrichmentCount,
        ]);
    }
}
