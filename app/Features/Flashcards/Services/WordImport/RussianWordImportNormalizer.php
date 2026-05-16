<?php

namespace App\Features\Flashcards\Services\WordImport;

use App\Features\Flashcards\Services\TranscriptionRuNormalizer;
use App\Features\Flashcards\Support\FormTypeCatalog;
use App\Features\Flashcards\Support\ShoreshRootNormalizer;

/**
 * Normalizes LLM JSON for Russian-first word import into a common candidate shape.
 */
final class RussianWordImportNormalizer
{
    /**
     * @param  array<string, mixed>  $inner  Root JSON object from the model
     * @return array{candidates: list<array<string, mixed>>}|null
     */
    public static function fromLlmArray(array $inner): ?array
    {
        if (isset($inner['candidates']) && is_array($inner['candidates'])) {
            $candidates = [];
            foreach ($inner['candidates'] as $c) {
                if (! is_array($c)) {
                    continue;
                }
                $norm = self::normalizeCandidate($c);
                if ($norm !== null) {
                    $candidates[] = $norm;
                }
            }

            return $candidates === [] ? null : ['candidates' => $candidates];
        }

        $ft = trim((string) ($inner['form_text'] ?? ''));
        if ($ft === '') {
            return null;
        }
        $one = self::normalizeCandidate($inner);

        return $one === null ? null : ['candidates' => [$one]];
    }

    /**
     * @param  array<string, mixed>  $c
     * @return array<string, mixed>|null
     */
    public static function normalizeCandidate(array $c): ?array
    {
        $formText = trim((string) ($c['form_text'] ?? ''));
        if ($formText === '') {
            return null;
        }

        $entries = [];
        if (isset($c['entries']) && is_array($c['entries'])) {
            foreach ($c['entries'] as $entry) {
                if (! is_array($entry)) {
                    continue;
                }
                $translation = isset($entry['translation_ru']) ? (string) $entry['translation_ru'] : '';
                if (trim($translation) === '') {
                    continue;
                }
                $entryOut = [
                    'translation_ru' => $translation,
                    'form_type' => FormTypeCatalog::resolveFromImportEntry($entry),
                ];
                if (isset($entry['transcription_ru']) && trim((string) $entry['transcription_ru']) !== '') {
                    $entryOut['transcription_ru'] = TranscriptionRuNormalizer::normalize((string) $entry['transcription_ru']);
                }
                $entries[] = $entryOut;
            }
        }

        $frequencyRank = $c['frequency_rank'] ?? ($c['frequencyRank'] ?? null);
        $frequencyPerMillion = $c['frequency_per_million'] ?? ($c['frequencyPerMillion'] ?? null);

        $transcriptionRu = isset($c['transcription_ru']) ? (string) $c['transcription_ru'] : null;
        if ($transcriptionRu !== null && $transcriptionRu !== '') {
            $transcriptionRu = TranscriptionRuNormalizer::normalize($transcriptionRu);
        } else {
            $transcriptionRu = null;
        }

        return [
            'form_text' => $formText,
            'transcription_ru' => $transcriptionRu,
            'shoresh_root' => ShoreshRootNormalizer::normalize(
                isset($c['shoresh_root']) && trim((string) $c['shoresh_root']) !== ''
                    ? (string) $c['shoresh_root']
                    : null
            ),
            'frequency_rank' => $frequencyRank !== null ? (float) $frequencyRank : null,
            'frequency_per_million' => $frequencyPerMillion !== null ? (float) $frequencyPerMillion : null,
            'entries' => $entries,
        ];
    }
}
