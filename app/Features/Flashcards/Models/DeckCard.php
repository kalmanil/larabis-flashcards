<?php

namespace App\Features\Flashcards\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeckCard extends Model
{
    protected $fillable = ['deck_id', 'hebrew_form_id'];

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function hebrewForm(): BelongsTo
    {
        return $this->belongsTo(HebrewForm::class, 'hebrew_form_id');
    }
}
