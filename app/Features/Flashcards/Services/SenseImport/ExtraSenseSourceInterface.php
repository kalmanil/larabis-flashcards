<?php

namespace App\Features\Flashcards\Services\SenseImport;

interface ExtraSenseSourceInterface
{
    public function getKey(): string;

    public function getLabel(): string;

    /**
     * @param  array<int, string|null>  $existingTranslationRuTexts
     * @return array{translation_ru: string, form_type: string|null, transcription_ru?: string}|null
     */
    public function fetchOne(string $hebrewFormText, array $existingTranslationRuTexts): ?array;
}
