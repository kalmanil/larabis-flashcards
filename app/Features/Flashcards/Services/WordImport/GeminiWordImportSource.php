<?php

namespace App\Features\Flashcards\Services\WordImport;

use App\Features\Flashcards\Services\TranscriptionRuNormalizer;
use App\Features\Flashcards\Support\FormTypeCatalog;
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

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key='.urlencode($apiKey);

        $userText = 'Hebrew word to analyze: '.$hebrewFormText;

        $payload = [
            'systemInstruction' => [
                'parts' => [
                    ['text' => FormTypeCatalog::wordImportSystemInstruction()],
                ],
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $userText],
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

        if (! $response->ok()) {
            return null;
        }

        $outer = $response->json();
        $text = $outer['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (! is_string($text) || trim($text) === '') {
            return null;
        }

        $inner = json_decode($text, true);
        if (! is_array($inner)) {
            return null;
        }

        $entries = [];
        if (isset($inner['entries']) && is_array($inner['entries'])) {
            foreach ($inner['entries'] as $entry) {
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
