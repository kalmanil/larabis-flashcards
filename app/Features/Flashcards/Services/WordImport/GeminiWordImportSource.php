<?php

namespace App\Features\Flashcards\Services\WordImport;

use App\Features\Flashcards\Services\TranscriptionRuNormalizer;
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
            . "'transcription_ru' (string): Russian transliteration/pronunciation, fully lowercase. Mark the stressed vowel using the Unicode combining acute accent (U+0301) placed immediately after that vowel only (e.g. shálom = s-h-a-acute-l-o-m). Use no other stress markers. "
            . "'shoresh_root' (string): The 2 or 4 letter Hebrew root (no hyphens - e.g., קדם). "
            . "'frequency_rank' (number): frequency rank of the word. "
            . "'frequency_per_million' (number): usage frequency per million words. "
            . "'entries' (array of objects): each object is one sense with exactly one most fitting Russian translation: "
            . "'translation_ru' (string): the single best Russian translation for this sense. "
            . "'form_type' (string): part of speech or grammatical form in English (e.g., adverb, noun (masc.), verb – hif'il infinitive). "
            . "'transcription_ru' (string, optional): only if this sense's pronunciation differs from the top-level 'transcription_ru'; same U+0301 stress rules. Omit if same as default. "
            . "Return only one translation per sense. Return ONLY valid JSON, with no extra commentary or code fences.";

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
                $entryOut = [
                    'translation_ru' => $translation,
                    'form_type' => isset($entry['form_type']) ? (string) $entry['form_type'] : null,
                ];
                if (isset($entry['transcription_ru']) && trim((string) $entry['transcription_ru']) !== '') {
                    $entryOut['transcription_ru'] = TranscriptionRuNormalizer::normalize((string) $entry['transcription_ru']);
                }
                $entries[] = $entryOut;
            }
        }

        $frequencyRank = $inner['frequency_rank'] ?? ($inner['frequencyRank'] ?? null);
        $frequencyPerMillion = $inner['frequency_per_million'] ?? ($inner['frequencyPerMillion'] ?? null);

        $transcriptionRu = isset($inner['transcription_ru']) ? (string) $inner['transcription_ru'] : null;
        if ($transcriptionRu !== null && $transcriptionRu !== '') {
            $transcriptionRu = TranscriptionRuNormalizer::normalize($transcriptionRu);
        }

        return [
            'transcription_ru' => $transcriptionRu,
            'shoresh_root' => isset($inner['shoresh_root']) ? (string) $inner['shoresh_root'] : null,
            'frequency_rank' => $frequencyRank !== null ? (float) $frequencyRank : null,
            'frequency_per_million' => $frequencyPerMillion !== null ? (float) $frequencyPerMillion : null,
            'entries' => $entries,
        ];
    }
}

