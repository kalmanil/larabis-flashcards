<?php

namespace App\Features\Flashcards\Services\WordImport;

interface WordImportSourceInterface
{
    /**
     * Unique source identifier (e.g. 'wiktionary', 'database').
     */
    public function getKey(): string;

    /**
     * Display name for the UI.
     */
    public function getLabel(): string;

    /**
     * Fetch word data for the given Hebrew form text.
     * Returns array with keys: transcription_ru, translations_ru, shoresh_root, form_type.
     * Missing keys can be omitted.
     */
    public function fetch(string $hebrewFormText): ?array;
}
