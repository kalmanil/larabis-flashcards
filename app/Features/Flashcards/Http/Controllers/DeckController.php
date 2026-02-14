<?php

namespace App\Features\Flashcards\Http\Controllers;

use App\Features\Flashcards\Models\Deck;
use App\Features\Flashcards\Models\HebrewForm;
use App\Helpers\TenancyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeckController
{
    protected function ensureDefaultDeck(): Deck
    {
        $user = Auth::user();
        $deck = $user->decks()->where('is_default', true)->first();

        if (!$deck) {
            $deck = $user->decks()->create([
                'name' => 'My deck',
                'slug' => 'default',
                'is_default' => true,
            ]);
        }

        return $deck;
    }

    public function index(Request $request)
    {
        $deck = $this->ensureDefaultDeck();
        return redirect()->route('flashcards.decks.show', $deck);
    }

    public function show(Deck $deck)
    {
        if ($deck->user_id !== Auth::id()) {
            abort(403);
        }

        $deck->load(['deckCards.hebrewForm.translations.language', 'deckCards.hebrewForm.shoresh']);

        return TenancyHelper::view('flashcards.decks.show', [
            'deck' => $deck,
        ]);
    }

    public function removeCard(Deck $deck, HebrewForm $hebrewForm)
    {
        if ($deck->user_id !== Auth::id()) {
            abort(403);
        }

        $deck->deckCards()->where('hebrew_form_id', $hebrewForm->id)->delete();

        return redirect()->back()->with('success', 'Removed from deck.');
    }
}
