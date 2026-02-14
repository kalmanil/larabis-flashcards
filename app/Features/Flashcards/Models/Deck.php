<?php

namespace App\Features\Flashcards\Models;

use App\Features\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deck extends Model
{
    protected $fillable = ['user_id', 'name', 'slug', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deckCards(): HasMany
    {
        return $this->hasMany(DeckCard::class);
    }

    public function userCardProgress(): HasMany
    {
        return $this->hasMany(UserCardProgress::class, 'deck_id');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDefaultDeck($query, $userId)
    {
        return $query->where('user_id', $userId)->where('is_default', true);
    }
}
