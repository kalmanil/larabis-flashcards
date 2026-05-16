<?php

namespace App\Features\Flashcards\Support;

/**
 * Canonical stored roots: contiguous Hebrew letters, medial (non-sofit) forms only.
 */
final class ShoreshRootNormalizer
{
    /** @var array<string, string> */
    private const SOFIT_TO_MEDIAL = [
        'ך' => 'כ',
        'ם' => 'מ',
        'ן' => 'נ',
        'ף' => 'פ',
        'ץ' => 'צ',
    ];

    /**
     * Strip punctuation between letters, niqqud, and map final forms to medial.
     * Returns null if nothing usable remains.
     */
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $s = trim($value);
        if ($s === '') {
            return null;
        }

        $s = preg_replace('/[\x{0591}-\x{05C7}\x{05F3}\x{05F4}]/u', '', $s) ?? $s;

        if (preg_match_all('/[\x{05D0}-\x{05EA}]/u', $s, $matches) === false || $matches[0] === []) {
            return null;
        }

        $out = '';
        foreach ($matches[0] as $ch) {
            $out .= self::SOFIT_TO_MEDIAL[$ch] ?? $ch;
        }

        return $out !== '' ? $out : null;
    }
}
