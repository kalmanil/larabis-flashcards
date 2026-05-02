<?php

namespace App\Features\Flashcards\Services\SenseImport;

use App\Features\Flashcards\Models\HebrewForm;
use App\Features\Flashcards\Services\TranscriptionRuNormalizer;

class DatabaseExtraSenseSource implements ExtraSenseSourceInterface
{
    public function getKey(): string
    {
        return 'db';
    }

    public function getLabel(): string
    {
        return 'Database';
    }

    public function fetchOne(string $hebrewFormText, array $existingTranslationRuTexts): ?array
    {
        $exclude = RuTranslationCompare::excludeSet($existingTranslationRuTexts);

        $form = HebrewForm::query()
            ->where('form_text', $hebrewFormText)
            ->with([
                'translations' => function ($q) {
                    $q->whereHas('language', function ($l) {
                        $l->where('code', 'ru');
                    })->orderByPivot('sense_order');
                },
            ])
            ->first();

        if ($form === null) {
            return null;
        }

        foreach ($form->translations as $t) {
            $text = trim((string) $t->text);
            if ($text === '') {
                continue;
            }
            if (isset($exclude[RuTranslationCompare::normalize($text)])) {
                continue;
            }

            $entry = [
                'translation_ru' => $text,
                'form_type' => isset($t->pivot->form_type) && trim((string) $t->pivot->form_type) !== ''
                    ? (string) $t->pivot->form_type
                    : null,
            ];
            $pivotTr = isset($t->pivot->transcription_ru) ? trim((string) $t->pivot->transcription_ru) : '';
            if ($pivotTr !== '') {
                $entry['transcription_ru'] = TranscriptionRuNormalizer::normalize($pivotTr);
            }

            return $entry;
        }

        return null;
    }
}
