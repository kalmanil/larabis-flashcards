<?php

namespace App\Features\Flashcards\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HebrewForm extends Model
{
    protected $fillable = [
        'shoresh_id',
        'form_text',
        'form_type',
        'transcription_ru',
        'transcription_en',
        'frequency_rank',
        'frequency_per_million',
    ];

    protected $casts = [
        'frequency_rank' => 'integer',
        'frequency_per_million' => 'float',
    ];

    public function shoresh(): BelongsTo
    {
        return $this->belongsTo(Shoresh::class, 'shoresh_id');
    }

    public function translations(): BelongsToMany
    {
        return $this->belongsToMany(Translation::class, 'hebrew_form_translation')
            ->withTimestamps();
    }

    public function deckCards(): HasMany
    {
        return $this->hasMany(DeckCard::class, 'hebrew_form_id');
    }
}
