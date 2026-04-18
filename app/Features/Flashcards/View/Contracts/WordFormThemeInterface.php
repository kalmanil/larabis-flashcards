<?php

declare(strict_types=1);

namespace App\Features\Flashcards\View\Contracts;

/**
 * View-specific Tailwind tokens for flashcards word forms.
 * Implementations live under App\Features\Flashcards\View\Ui\{Default|Admin}.
 */
interface WordFormThemeInterface
{
    /**
     * @return array<string, string>
     */
    public function variables(): array;
}
