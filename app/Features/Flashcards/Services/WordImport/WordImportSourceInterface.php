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
     *
     * Expected return shape:
     * [
     *   'transcription_ru' => string|null,
     *   'shoresh_root' => string|null,
     *   'frequency_rank' => int|float|null,
     *   'frequency_per_million' => int|float|null,
     *   'entries' => [
     *     [
     *       'translation_ru' => string,
     *       'form_type' => string|null,
     *       'transcription_ru' => string|null (optional per-sense override),
     *     ],
     *     ...
     *   ],
     * ]
     *
     * Missing keys can be omitted.
     */
    public function fetch(string $hebrewFormText): ?array;
}
