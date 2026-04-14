<?php

namespace App\Features\Flashcards\Http\Controllers\Staff;

use App\Features\Flashcards\Models\Deck;
use App\Helpers\TenancyHelper;

/**
 * Read-only overview of all users' decks (staff: subadmin + superadmin).
 */
class StaffDeckOverviewController
{
    public function index()
    {
        $decks = Deck::query()
            ->with('user:id,name,email')
            ->withCount('deckCards')
            ->orderBy('user_id')
            ->orderBy('name')
            ->paginate(30);

        return TenancyHelper::view('flashcards.staff.decks', [
            'decks' => $decks,
        ]);
    }
}
