<?php

namespace App\Features\Flashcards\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Translation extends Model
{
    protected $fillable = ['language_id', 'text'];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function hebrewForms(): BelongsToMany
    {
        return $this->belongsToMany(HebrewForm::class, 'hebrew_form_translation')
            ->withTimestamps();
    }
}
