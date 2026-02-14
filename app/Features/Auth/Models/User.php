<?php

namespace App\Features\Auth\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relationship to decks
     */
    public function decks()
    {
        return $this->hasMany(\App\Features\Flashcards\Models\Deck::class);
    }

    /**
     * Relationship to card progress
     */
    public function cardProgress()
    {
        return $this->hasMany(\App\Features\Flashcards\Models\UserCardProgress::class);
    }

    /**
     * Relationship to social accounts
     */
    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Get social account by provider
     */
    public function getSocialAccount($provider)
    {
        return $this->socialAccounts()
            ->where('provider', $provider)
            ->first();
    }

    /**
     * Check if user has social account
     */
    public function hasSocialAccount($provider): bool
    {
        return $this->socialAccounts()
            ->where('provider', $provider)
            ->exists();
    }
}
