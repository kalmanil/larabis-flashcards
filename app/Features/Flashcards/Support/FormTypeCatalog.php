<?php

namespace App\Features\Flashcards\Support;

/**
 * Loads tenant-local config/form_types.php and resolves labels for prompts, validation, and storage.
 */
final class FormTypeCatalog
{
    /** @var array{allowed: list<string>, aliases: array<string, string>}|null */
    private static ?array $config = null;

    /** @var array<string, string>|null lowercase alias => canonical allowed */
    private static ?array $aliasToCanonical = null;

    /**
     * @return list<string>
     */
    public static function allowed(): array
    {
        $allowed = self::config()['allowed'] ?? [];

        return array_values($allowed);
    }

    /**
     * Compact id=label list for LLM prompts (token-efficient vs JSON array of strings).
     */
    public static function formTypeIdLegend(): string
    {
        $parts = [];
        foreach (self::allowed() as $i => $label) {
            $parts[] = ($i + 1).'='.$label;
        }

        return implode('; ', $parts);
    }

    /**
     * Static instructions for full word import (Gemini systemInstruction / OpenAI instructions).
     */
    public static function wordImportSystemInstruction(): string
    {
        $factors = FormTypeComposer::promptFactorTables();

        return 'You output only valid JSON (no markdown fences, no commentary).'
            ."\n\nSchema for Hebrew word analysis:\n"
            ."- transcription_ru: string. Practical Russian transliteration in Cyrillic (Russian Hebrew textbook style), not Latin/IPA. Optional lone lowercase h for voiceless glottal ה. Fully lowercase. Stress: Unicode combining acute U+0301 only, immediately after the stressed vowel (e.g. шало́м).\n"
            .'- shoresh_root: string, 2–4 Hebrew letters, no ASCII hyphens in the value.'
            ."\n- frequency_rank: number (estimate ok if uncertain).\n"
            ."- frequency_per_million: number (estimate ok if uncertain).\n"
            ."- entries: array of senses. Each object must have translation_ru (string) plus factor fields below. Optional transcription_ru (per-sense) if pronunciation differs from top-level.\n\n"
            .$factors
            ."\n\nEach entry JSON must include pos_id and any modifiers required by that pos (see rules). Do not send form_type, form_type_id, or merged labels.\n"
            .'Example entry shapes: {"translation_ru":"…","pos_id":2,"verb_binyan_id":5,"verb_shape_id":2} for hif\'il infinitive; add verb_shape_id 3 and verb_binyan_id 6 for huf\'al past (3ms).'
            ."\n\nReturn one translation_ru per sense. JSON only.";
    }

    /**
     * Static instructions for one extra sense (Gemini systemInstruction / OpenAI instructions).
     */
    public static function extraSenseSystemInstruction(): string
    {
        $factors = FormTypeComposer::promptFactorTables();

        return 'You output only valid JSON (no markdown fences, no commentary).'
            ."\n\nReturn exactly { \"entry\": { \"translation_ru\": string, ...factors... , \"transcription_ru\"?: string } } or { \"entry\": null }.\n"
            .$factors
            ."\n\nDo not send form_type or form_type_id. Do not repeat or paraphrase Russian glosses supplied in the user message.";
    }

    /**
     * @deprecated Legacy flat id list; prefer factorized {@see FormTypeComposer}.
     */
    public static function promptConstraint(): string
    {
        return 'Each sense must include pos_id and optional modifier fields per the flashcards form-type factor rules. '
            .'Do not use merged form_type strings; use factors only. Legacy form_type_id is still accepted server-side if present.';
    }

    /**
     * Normalize common LLM typography drift before matching (hyphens, apostrophes, spaces).
     */
    public static function normalizeDrift(string $value): string
    {
        $s = $value;
        $s = preg_replace('/[\x{2013}\x{2014}\x{2212}]/u', '-', $s) ?? $s;
        $s = preg_replace('/\x{00A0}/u', ' ', $s) ?? $s;
        $s = preg_replace('/[\x{2019}\x{2018}\x{02BC}\x{02BB}]/u', "'", $s) ?? $s;
        $s = trim($s);
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;

        return $s;
    }

    /**
     * Map catalog position (1-based) to canonical label.
     */
    public static function fromFormTypeId(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value)) {
            $value = trim($value);
            if ($value === '' || ! ctype_digit($value)) {
                return null;
            }
        }
        if (! is_numeric($value)) {
            return null;
        }
        $i = (int) $value;
        if ($i < 1) {
            return null;
        }
        $allowed = self::allowed();
        if ($i > count($allowed)) {
            return null;
        }

        return $allowed[$i - 1];
    }

    /**
     * Resolve form type from a raw LLM entry: factorized fields first, then legacy id/string.
     *
     * @param  array<string, mixed>  $entry
     */
    public static function resolveFromImportEntry(array $entry): ?string
    {
        $composed = FormTypeComposer::compose($entry);
        if ($composed !== null) {
            $verified = self::canonical($composed);
            if ($verified !== null) {
                return $verified;
            }
        }

        if (array_key_exists('form_type_id', $entry)) {
            $byId = self::fromFormTypeId($entry['form_type_id']);
            if ($byId !== null) {
                return $byId;
            }
        }

        if (isset($entry['form_type']) && is_string($entry['form_type'])) {
            $ft = trim($entry['form_type']);
            if ($ft !== '' && ctype_digit($ft)) {
                $byId = self::fromFormTypeId((int) $ft);
                if ($byId !== null) {
                    return $byId;
                }
            }
        }

        if (isset($entry['form_type']) && is_string($entry['form_type'])) {
            return self::canonical($entry['form_type']);
        }

        return null;
    }

    /**
     * Map user or model input to a canonical allowed string, or null if unknown/empty.
     */
    public static function canonical(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = self::normalizeDrift(trim($value));
        if ($trimmed === '') {
            return null;
        }

        foreach (self::allowed() as $canonical) {
            if ($trimmed === $canonical) {
                return $canonical;
            }
        }

        $lower = mb_strtolower($trimmed);
        if (isset(self::aliasMap()[$lower])) {
            return self::aliasMap()[$lower];
        }

        foreach (self::allowed() as $canonical) {
            if (mb_strtolower($canonical) === $lower) {
                return $canonical;
            }
        }

        return null;
    }

    /**
     * @internal
     */
    public static function configPath(): string
    {
        return dirname(__DIR__, 4).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'form_types.php';
    }

    /**
     * @return array{allowed: list<string>, aliases: array<string, string>}
     */
    private static function config(): array
    {
        if (self::$config !== null) {
            return self::$config;
        }

        $path = self::configPath();
        if (! is_file($path)) {
            return self::$config = [
                'allowed' => FormTypeComposer::allComposedLabels(),
                'aliases' => [],
            ];
        }

        /** @var mixed $loaded */
        $loaded = require $path;
        if (! is_array($loaded)) {
            return self::$config = [
                'allowed' => FormTypeComposer::allComposedLabels(),
                'aliases' => [],
            ];
        }

        $composed = FormTypeComposer::allComposedLabels();
        $extraAllowed = self::normalizeConfigStringList($loaded['extra_allowed'] ?? []);
        $legacyAllowed = self::normalizeConfigStringList($loaded['allowed'] ?? []);

        $allowedList = array_values(array_unique(array_merge($composed, $extraAllowed, $legacyAllowed)));
        sort($allowedList, SORT_STRING);

        $aliases = $loaded['aliases'] ?? [];
        if (! is_array($aliases)) {
            $aliases = [];
        }

        $aliasMap = [];
        foreach ($aliases as $from => $to) {
            if (! is_string($from) || ! is_string($to)) {
                continue;
            }
            $fromT = trim($from);
            $toT = trim($to);
            if ($fromT === '' || $toT === '') {
                continue;
            }
            $aliasMap[$fromT] = $toT;
        }

        return self::$config = ['allowed' => $allowedList, 'aliases' => $aliasMap];
    }

    /**
     * @return list<string>
     */
    private static function normalizeConfigStringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $out = [];
        foreach ($value as $item) {
            if (is_string($item) && trim($item) !== '') {
                $out[] = trim($item);
            }
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    private static function aliasMap(): array
    {
        if (self::$aliasToCanonical !== null) {
            return self::$aliasToCanonical;
        }

        $allowedExact = [];
        foreach (self::allowed() as $c) {
            $allowedExact[$c] = true;
        }

        $resolveTarget = function (string $target) use ($allowedExact): ?string {
            if (isset($allowedExact[$target])) {
                return $target;
            }
            foreach (array_keys($allowedExact) as $c) {
                if (mb_strtolower((string) $c) === mb_strtolower($target)) {
                    return (string) $c;
                }
            }

            return null;
        };

        $out = [];
        foreach (self::config()['aliases'] as $alias => $target) {
            $canonical = $resolveTarget($target);
            if ($canonical !== null) {
                $out[mb_strtolower($alias)] = $canonical;
            }
        }

        return self::$aliasToCanonical = $out;
    }
}
