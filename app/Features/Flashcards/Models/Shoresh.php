<?php

namespace App\Features\Flashcards\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shoresh extends Model
{
    protected $table = 'shoresh';

    protected $fillable = ['root'];

    public function hebrewForms(): HasMany
    {
        return $this->hasMany(HebrewForm::class, 'shoresh_id');
    }
}
