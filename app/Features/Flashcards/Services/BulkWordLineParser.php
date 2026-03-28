<?php

namespace App\Features\Flashcards\Services;

final class BulkWordLineParser
{
    /**
     * Split textarea by newlines, trim, drop empties, dedupe preserving first occurrence.
     *
     * @return list<string>
     */
    public static function uniqueLines(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $seen = [];
        $out = [];

        foreach ($lines as $line) {
            $w = trim((string) $line);
            if ($w === '') {
                continue;
            }
            if (isset($seen[$w])) {
                continue;
            }
            $seen[$w] = true;
            $out[] = $w;
        }

        return $out;
    }
}
