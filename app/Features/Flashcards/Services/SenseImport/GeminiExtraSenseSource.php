<?php

namespace App\Features\Flashcards\Services\SenseImport;

use App\Features\Flashcards\Services\TranscriptionRuNormalizer;
use Illuminate\Support\Facades\Http;

class GeminiExtraSenseSource implements ExtraSenseSourceInterface
{
    public function getKey(): string
    {
        return 'gemini';
    }

    public function getLabel(): string
    {
        return 'Gemini AI';
    }

    public function fetchOne(string $hebrewFormText, array $existingTranslationRuTexts): ?array
    {
        $apiKey = (string) config('services.gemini.key', env('GEMINI_API_KEY'));

        if ($apiKey === '') {
            return null;
        }

        $exclude = RuTranslationCompare::excludeSet($existingTranslationRuTexts);
        $listed = [];
        foreach ($existingTranslationRuTexts as $ex) {
            $t = trim((string) $ex);
            if ($t !== '') {
                $listed[] = $t;
            }
        }
        $listJson = json_encode($listed, JSON_UNESCAPED_UNICODE);

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key='.urlencode($apiKey);

        $prompt = "Analyze the Hebrew word '".$hebrewFormText."'. The following Russian glosses are already listed — do not repeat or merely paraphrase them: "
            .$listJson
            .". Return ONLY JSON: { \"entry\": { \"translation_ru\" (string), \"form_type\" (string), optional \"transcription_ru\" (string) } } for exactly one additional plausible sense. "
            ."For optional per-sense \"transcription_ru\": practical Russian transliteration in Cyrillic only (as in Russian Hebrew textbooks)—not English/Latin romanization, not IPA. "
            .'Optional lone lowercase h for voiceless glottal ה if needed. Fully lowercase. Mark stress with Unicode combining acute (U+0301) immediately after the stressed vowel only. '
            ."If there is no reasonable extra sense, return { \"entry\": null }. Return ONLY valid JSON, with no extra commentary or code fences.";

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

        $entry = $inner['entry'] ?? null;
        if ($entry === null) {
            return null;
        }
        if (! is_array($entry)) {
            return null;
        }

        $translation = isset($entry['translation_ru']) ? (string) $entry['translation_ru'] : '';
        if (trim($translation) === '') {
            return null;
        }
        if (isset($exclude[RuTranslationCompare::normalize($translation)])) {
            return null;
        }

        $out = [
            'translation_ru' => $translation,
            'form_type' => isset($entry['form_type']) && trim((string) $entry['form_type']) !== ''
                ? (string) $entry['form_type']
                : null,
        ];
        if (isset($entry['transcription_ru']) && trim((string) $entry['transcription_ru']) !== '') {
            $out['transcription_ru'] = TranscriptionRuNormalizer::normalize((string) $entry['transcription_ru']);
        }

        return $out;
    }
}
