<?php

namespace App\Features\Flashcards\Services\SenseImport;

use App\Features\Flashcards\Services\TranscriptionRuNormalizer;
use Illuminate\Support\Facades\Http;

class OpenAiExtraSenseSource implements ExtraSenseSourceInterface
{
    public function getKey(): string
    {
        return 'openai';
    }

    public function getLabel(): string
    {
        return 'OpenAI';
    }

    public function fetchOne(string $hebrewFormText, array $existingTranslationRuTexts): ?array
    {
        $apiKey = (string) config('services.openai.key', env('OPENAI_API_KEY'));

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

        $url = 'https://api.openai.com/v1/responses';

        $prompt = "Analyze the Hebrew word '".$hebrewFormText."'. The following Russian glosses are already listed — do not repeat or merely paraphrase them: "
            .$listJson
            .". Return ONLY JSON: { \"entry\": { \"translation_ru\" (string), \"form_type\" (string), optional \"transcription_ru\" (string) } } for exactly one additional plausible sense. "
            ."For optional per-sense \"transcription_ru\": practical Russian transliteration in Cyrillic only (as in Russian Hebrew textbooks)—not English/Latin romanization, not IPA. "
            .'Optional lone lowercase h for voiceless glottal ה if needed. Fully lowercase. Mark stress with Unicode combining acute (U+0301) immediately after the stressed vowel only. '
            ."If there is no reasonable extra sense, return { \"entry\": null }. Return ONLY valid JSON, with no extra commentary or code fences.";

        $payload = [
            'model' => 'gpt-5.4',
            'input' => $prompt,
        ];

        $response = Http::withoutVerifying()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$apiKey,
            ])
            ->post($url, $payload);

        if (! $response->ok()) {
            return null;
        }

        $data = $response->json();
        $text = $this->extractOutputTextFromResponses($data);

        if (! is_string($text) || trim($text) === '') {
            return null;
        }

        $text = $this->stripJsonFences($text);
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

    /**
     * @param  array<string, mixed>  $data
     */
    private function extractOutputTextFromResponses(?array $data): ?string
    {
        if (! is_array($data) || ! isset($data['output']) || ! is_array($data['output'])) {
            return null;
        }

        foreach ($data['output'] as $block) {
            if (($block['type'] ?? '') !== 'message') {
                continue;
            }
            foreach ($block['content'] ?? [] as $part) {
                if (($part['type'] ?? '') === 'output_text' && isset($part['text'])) {
                    return (string) $part['text'];
                }
            }
        }

        return null;
    }

    private function stripJsonFences(string $text): string
    {
        $t = trim($text);
        if (! str_starts_with($t, '```')) {
            return $t;
        }
        $t = preg_replace('/^```(?:json)?\s*/i', '', $t) ?? $t;
        $t = preg_replace('/\s*```\s*$/', '', $t) ?? $t;

        return trim($t);
    }
}
