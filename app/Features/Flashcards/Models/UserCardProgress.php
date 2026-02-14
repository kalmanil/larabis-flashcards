<?php

namespace App\Features\Flashcards\Models;

use App\Features\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCardProgress extends Model
{
    protected $table = 'user_card_progress';

    protected $fillable = ['user_id', 'deck_id', 'hebrew_form_id', 'known', 'last_seen_at'];

    protected $casts = [
        'known' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function hebrewForm(): BelongsTo
    {
        return $this->belongsTo(HebrewForm::class, 'hebrew_form_id');
    }
}
