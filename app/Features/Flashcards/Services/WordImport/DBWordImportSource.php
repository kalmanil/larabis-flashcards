<?php

namespace App\Features\Flashcards\Services\WordImport;

use App\Features\Flashcards\Models\HebrewForm;
use App\Features\Flashcards\Services\TranscriptionRuNormalizer;

class DBWordImportSource implements WordImportSourceInterface
{
    public function getKey(): string
    {
        return 'db';
    }

    public function getLabel(): string
    {
        return 'Database';
    }

    public function fetch(string $hebrewFormText): ?array
    {
        $form = HebrewForm::query()
            ->where('form_text', $hebrewFormText)
            ->with([
                'shoresh',
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

        $entries = [];
        foreach ($form->translations as $t) {
            $text = trim((string) $t->text);
            if ($text === '') {
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
            $entries[] = $entry;
        }

        $transcriptionRu = $form->transcription_ru;
        if ($transcriptionRu !== null && $transcriptionRu !== '') {
            $transcriptionRu = TranscriptionRuNormalizer::normalize((string) $transcriptionRu);
        } else {
            $transcriptionRu = null;
        }

        $shoreshRoot = $form->shoresh !== null ? trim((string) $form->shoresh->root) : '';
        $frequencyRank = $form->frequency_rank;
        $frequencyPerMillion = $form->frequency_per_million;

        return [
            'transcription_ru' => $transcriptionRu,
            'shoresh_root' => $shoreshRoot !== '' ? $shoreshRoot : null,
            'frequency_rank' => $frequencyRank !== null ? (float) $frequencyRank : null,
            'frequency_per_million' => $frequencyPerMillion !== null ? (float) $frequencyPerMillion : null,
            'entries' => $entries,
        ];
    }
}
