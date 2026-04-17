<?php

namespace App\Features\Flashcards\Services\WordImport;

use App\Features\Flashcards\Services\TranscriptionRuNormalizer;

/**
 * Runs Gemini and OpenAI import, then merges into one payload (simple heuristics).
 */
class UnitedWordImportSource implements WordImportSourceInterface
{
    public function __construct(
        private GeminiWordImportSource $gemini,
        private OpenAiWordImportSource $openai,
    ) {}

    public function getKey(): string
    {
        return 'united';
    }

    public function getLabel(): string
    {
        return 'United (Gemini + OpenAI)';
    }

    public function fetch(string $hebrewFormText): ?array
    {
        $g = $this->gemini->fetch($hebrewFormText);
        $o = $this->openai->fetch($hebrewFormText);

        if ($g === null && $o === null) {
            return null;
        }

        if ($g === null) {
            return $o;
        }

        if ($o === null) {
            return $g;
        }

        return $this->merge($g, $o);
    }

    /**
     * @param  array<string, mixed>  $gemini
     * @param  array<string, mixed>  $openai
     * @return array<string, mixed>
     */
    private function merge(array $gemini, array $openai): array
    {
        $tG = $gemini['transcription_ru'] ?? null;
        $tO = $openai['transcription_ru'] ?? null;
        $transcription = $this->pickTranscription(is_string($tG) ? $tG : null, is_string($tO) ? $tO : null);
        if ($transcription !== null && $transcription !== '') {
            $transcription = TranscriptionRuNormalizer::normalize($transcription);
        }

        $shoresh = $this->pickShoresh(
            isset($gemini['shoresh_root']) ? (string) $gemini['shoresh_root'] : '',
            isset($openai['shoresh_root']) ? (string) $openai['shoresh_root'] : '',
        );

        $gEntries = isset($gemini['entries']) && is_array($gemini['entries']) ? $gemini['entries'] : [];
        $oEntries = isset($openai['entries']) && is_array($openai['entries']) ? $openai['entries'] : [];

        return [
            'transcription_ru' => $transcription,
            'shoresh_root' => $shoresh !== '' ? $shoresh : null,
            'frequency_rank' => $this->averageNullable(
                $gemini['frequency_rank'] ?? null,
                $openai['frequency_rank'] ?? null,
            ),
            'frequency_per_million' => $this->averageNullable(
                $gemini['frequency_per_million'] ?? null,
                $openai['frequency_per_million'] ?? null,
            ),
            'entries' => $this->mergeEntries($gEntries, $oEntries),
        ];
    }

    private function pickTranscription(?string $a, ?string $b): ?string
    {
        $a = $a !== null ? trim($a) : '';
        $b = $b !== null ? trim($b) : '';
        if ($a === '' && $b === '') {
            return null;
        }
        if ($a === '') {
            return $b;
        }
        if ($b === '') {
            return $a;
        }
        if ($a === $b) {
            return $a;
        }

        $cyrA = $this->hasCyrillic($a);
        $cyrB = $this->hasCyrillic($b);
        if ($cyrA && ! $cyrB) {
            return $a;
        }
        if ($cyrB && ! $cyrA) {
            return $b;
        }

        return mb_strlen($a) >= mb_strlen($b) ? $a : $b;
    }

    private function hasCyrillic(string $s): bool
    {
        return (bool) preg_match('/\p{Cyrillic}/u', $s);
    }

    private function pickShoresh(string $g, string $o): string
    {
        $g = trim($g);
        $o = trim($o);
        if ($g === '' && $o === '') {
            return '';
        }
        if ($g === '') {
            return $o;
        }
        if ($o === '') {
            return $g;
        }
        if ($g === $o) {
            return $g;
        }

        return $g;
    }

    private function averageNullable(mixed $a, mixed $b): ?float
    {
        $na = is_numeric($a) ? (float) $a : null;
        $nb = is_numeric($b) ? (float) $b : null;
        if ($na !== null && $nb !== null) {
            return ($na + $nb) / 2.0;
        }

        return $na ?? $nb;
    }

    /**
     * @param  array<int, mixed>  $gEntries
     * @param  array<int, mixed>  $oEntries
     * @return array<int, array<string, mixed>>
     */
    private function mergeEntries(array $gEntries, array $oEntries): array
    {
        $n = max(count($gEntries), count($oEntries));
        $out = [];
        for ($i = 0; $i < $n; $i++) {
            $ge = $gEntries[$i] ?? null;
            $oe = $oEntries[$i] ?? null;
            if (! is_array($ge) && ! is_array($oe)) {
                continue;
            }
            if (! is_array($ge)) {
                $merged = $this->normalizeMergedEntry($oe);
            } elseif (! is_array($oe)) {
                $merged = $this->normalizeMergedEntry($ge);
            } else {
                $merged = $this->mergeEntryRow($ge, $oe);
            }
            if ($merged !== null) {
                $out[] = $merged;
            }
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>|null
     */
    private function normalizeMergedEntry(array $row): ?array
    {
        $translation = isset($row['translation_ru']) ? trim((string) $row['translation_ru']) : '';
        if ($translation === '') {
            return null;
        }
        $entry = [
            'translation_ru' => $translation,
            'form_type' => isset($row['form_type']) ? (string) $row['form_type'] : null,
        ];
        if (isset($row['transcription_ru']) && trim((string) $row['transcription_ru']) !== '') {
            $entry['transcription_ru'] = TranscriptionRuNormalizer::normalize((string) $row['transcription_ru']);
        }

        return $entry;
    }

    /**
     * @param  array<string, mixed>  $g
     * @param  array<string, mixed>  $o
     * @return array<string, mixed>|null
     */
    private function mergeEntryRow(array $g, array $o): ?array
    {
        $tg = isset($g['translation_ru']) ? trim((string) $g['translation_ru']) : '';
        $to = isset($o['translation_ru']) ? trim((string) $o['translation_ru']) : '';
        $translation = $tg !== '' && $to !== ''
            ? (mb_strlen($tg) >= mb_strlen($to) ? $tg : $to)
            : ($tg !== '' ? $tg : $to);
        if ($translation === '') {
            return null;
        }

        $fg = isset($g['form_type']) ? trim((string) $g['form_type']) : '';
        $fo = isset($o['form_type']) ? trim((string) $o['form_type']) : '';
        $formType = $fg !== '' ? $fg : ($fo !== '' ? $fo : null);

        $entry = [
            'translation_ru' => $translation,
            'form_type' => $formType,
        ];

        $sg = isset($g['transcription_ru']) ? trim((string) $g['transcription_ru']) : '';
        $so = isset($o['transcription_ru']) ? trim((string) $o['transcription_ru']) : '';
        $senseT = $this->pickTranscription($sg !== '' ? $sg : null, $so !== '' ? $so : null);
        if ($senseT !== null && $senseT !== '') {
            $entry['transcription_ru'] = TranscriptionRuNormalizer::normalize($senseT);
        }

        return $entry;
    }
}
