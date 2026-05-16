<?php

namespace App\Features\Flashcards\Services\WordImport;

use App\Features\Flashcards\Models\HebrewForm;
use App\Features\Flashcards\Models\Language;
use App\Features\Flashcards\Models\Translation;
use App\Features\Flashcards\Services\TranscriptionRuNormalizer;
use App\Features\Flashcards\Support\ShoreshRootNormalizer;

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

        return $this->hebrewImportPayloadWithoutFormText($this->candidateFromHebrewForm($form));
    }

    public function fetchFromRussian(string $russianText): ?array
    {
        $lang = Language::query()->where('code', 'ru')->first();
        if ($lang === null) {
            return null;
        }

        $term = trim($russianText);
        if ($term === '') {
            return null;
        }

        $translation = Translation::query()
            ->where('language_id', $lang->id)
            ->where('text', $term)
            ->first();

        if ($translation === null) {
            return null;
        }

        $forms = $translation->hebrewForms()
            ->with([
                'shoresh',
                'translations' => function ($q) {
                    $q->whereHas('language', function ($l) {
                        $l->where('code', 'ru');
                    })->orderByPivot('sense_order');
                },
            ])
            ->orderBy('form_text')
            ->get();

        if ($forms->isEmpty()) {
            return null;
        }

        $candidates = [];
        foreach ($forms as $form) {
            $candidates[] = $this->candidateFromHebrewForm($form);
        }

        return ['candidates' => $candidates];
    }

    /**
     * @return array<string, mixed>
     */
    private function candidateFromHebrewForm(HebrewForm $form): array
    {
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
        $shoreshRootNorm = ShoreshRootNormalizer::normalize($shoreshRoot !== '' ? $shoreshRoot : null);
        $frequencyRank = $form->frequency_rank;
        $frequencyPerMillion = $form->frequency_per_million;

        return [
            'form_text' => (string) $form->form_text,
            'transcription_ru' => $transcriptionRu,
            'shoresh_root' => $shoreshRootNorm,
            'frequency_rank' => $frequencyRank !== null ? (float) $frequencyRank : null,
            'frequency_per_million' => $frequencyPerMillion !== null ? (float) $frequencyPerMillion : null,
            'entries' => $entries,
        ];
    }

    /**
     * @param  array<string, mixed>  $candidate
     * @return array<string, mixed>
     */
    private function hebrewImportPayloadWithoutFormText(array $candidate): array
    {
        unset($candidate['form_text']);

        return $candidate;
    }
}
