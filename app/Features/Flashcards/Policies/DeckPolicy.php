<?php

namespace App\Features\Flashcards\Policies;

use App\Features\Auth\Models\User;
use App\Features\Flashcards\Models\Deck;

class DeckPolicy
{
    public function view(User $user, Deck $deck): bool
    {
        return $user->id === $deck->user_id;
    }

    public function update(User $user, Deck $deck): bool
    {
        return $user->id === $deck->user_id;
    }

    public function delete(User $user, Deck $deck): bool
    {
        return $user->id === $deck->user_id;
    }
}
