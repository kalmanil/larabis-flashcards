<?php

namespace App\Features\Flashcards\Services\WordImport;

use Illuminate\Support\Facades\Http;

class GeminiWordImportSource implements WordImportSourceInterface
{
    public function getKey(): string
    {
        return 'gemini';
    }

    public function getLabel(): string
    {
        return 'Gemini AI';
    }

    public function fetch(string $hebrewFormText): ?array
    {
        $apiKey = (string) config('services.gemini.key', env('GEMINI_API_KEY'));

        if ($apiKey === '') {
            return null;
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . urlencode($apiKey);

        $prompt = "Analyze the Hebrew word '" . $hebrewFormText . "' and return a JSON object with exactly these keys: "
            . "'transcription_ru' (string): Russian transliteration/pronunciation, must be fully lowercase and the stressed vowel/letter must be marked with an acute accent (´) over it (e.g., prímer). "
            . "'shoresh_root' (string): The 2 or 4 letter Hebrew root (no hyphens - e.g., קדם). "
            . "'frequency_rank' (number): frequency rank of the word. "
            . "'frequency_per_million' (number): usage frequency per million words. "
            . "'entries' (array of objects): each object describes one main sense/translation and has: "
            . "'translation_ru' (string): Russian translation for this sense. "
            . "'form_type' (string): part of speech or grammatical/inflectional form for this sense in english (e.g., adverb, noun (masc.), verb – hif'il infinitive). "
            . "Return ONLY valid JSON, with no extra commentary or code fences.";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
            ],
        ];

        $response = Http::withoutVerifying()
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

        if (!$response->ok()) {
            return null;
        }

        $outer = $response->json();
        $text = $outer['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!is_string($text) || trim($text) === '') {
            return null;
        }

        $inner = json_decode($text, true);
        if (!is_array($inner)) {
            return null;
        }

        // Normalize keys and types to what the app expects.
        $entries = [];
        if (isset($inner['entries']) && is_array($inner['entries'])) {
            foreach ($inner['entries'] as $entry) {
                if (!is_array($entry)) {
                    continue;
                }
                $translation = isset($entry['translation_ru']) ? (string) $entry['translation_ru'] : '';
                if (trim($translation) === '') {
                    continue;
                }
                $entries[] = [
                    'translation_ru' => $translation,
                    'form_type' => isset($entry['form_type']) ? (string) $entry['form_type'] : null,
                ];
            }
        }

        $frequencyRank = $inner['frequency_rank'] ?? ($inner['frequencyRank'] ?? null);
        $frequencyPerMillion = $inner['frequency_per_million'] ?? ($inner['frequencyPerMillion'] ?? null);

        return [
            'transcription_ru' => isset($inner['transcription_ru']) ? (string) $inner['transcription_ru'] : null,
            'shoresh_root' => isset($inner['shoresh_root']) ? (string) $inner['shoresh_root'] : null,
            'frequency_rank' => $frequencyRank !== null ? (float) $frequencyRank : null,
            'frequency_per_million' => $frequencyPerMillion !== null ? (float) $frequencyPerMillion : null,
            'entries' => $entries,
        ];
    }
}

