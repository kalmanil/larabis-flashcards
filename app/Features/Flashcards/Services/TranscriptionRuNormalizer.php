<?php

namespace App\Features\Flashcards\Services;

class TranscriptionRuNormalizer
{
    private const COMBINING_ACUTE = "\u{0301}";

    /**
     * Normalize Russian transcription: trim and ensure at most one stress
     * (Unicode combining acute accent U+0301 after the stressed vowel).
     */
    public static function normalize(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value === null ? null : '';
        }

        $s = trim($value);
        if ($s === '') {
            return '';
        }

        $result = '';
        $seenStress = false;

        $len = mb_strlen($s);
        for ($i = 0; $i < $len; $i++) {
            $c = mb_substr($s, $i, 1);
            if ($c === self::COMBINING_ACUTE) {
                if (! $seenStress) {
                    $result .= $c;
                    $seenStress = true;
                }
            } else {
                $result .= $c;
            }
        }

        return $result;
    }
}
