<?php

namespace App\Features\Flashcards\Services\SenseImport;

final class RuTranslationCompare
{
    public static function normalize(string $text): string
    {
        return mb_strtolower(trim($text), 'UTF-8');
    }

    /**
     * @param  array<int, string|null>  $existing
     * @return array<string, true>
     */
    public static function excludeSet(array $existing): array
    {
        $set = [];
        foreach ($existing as $ex) {
            $t = trim((string) $ex);
            if ($t === '') {
                continue;
            }
            $set[self::normalize($t)] = true;
        }

        return $set;
    }
}
