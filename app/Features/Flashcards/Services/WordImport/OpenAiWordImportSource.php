<?php

namespace App\Features\Flashcards\Services\WordImport;

use App\Features\Flashcards\Services\TranscriptionRuNormalizer;
use Illuminate\Support\Facades\Http;

class OpenAiWordImportSource implements WordImportSourceInterface
{
    public function getKey(): string
    {
        return 'openai';
    }

    public function getLabel(): string
    {
        return 'OpenAI';
    }

    public function fetch(string $hebrewFormText): ?array
    {
        $apiKey = (string) config('services.openai.key', env('OPENAI_API_KEY'));

        if ($apiKey === '') {
            return null;
        }

        $url = 'https://api.openai.com/v1/responses';

        $prompt = "Analyze the Hebrew word '".$hebrewFormText."' and return a JSON object with exactly these keys: "
            ."'transcription_ru' (string): Practical Russian transliteration in Cyrillic only (as in Russian Hebrew textbooks)—not English/Latin romanization, not IPA. Optional lone lowercase h for voiceless glottal ה only if needed. Fully lowercase. Mark stress with Unicode combining acute (U+0301) immediately after the stressed vowel only (example: шало́м). No other stress markers. "
            ."'shoresh_root' (string): The 2 or 4 letter Hebrew root (no hyphens - e.g., קדם). "
            ."'frequency_rank' (number): frequency rank of the word. "
            ."'frequency_per_million' (number): usage frequency per million words. "
            ."'entries' (array of objects): each object is one sense with exactly one most fitting Russian translation: "
            ."'translation_ru' (string): the single best Russian translation for this sense. "
            ."'form_type' (string): part of speech or grammatical form in English (e.g., adverb, noun (masc.), verb – hif'il infinitive). "
            ."'transcription_ru' (string, optional): only if this sense's pronunciation differs from the top-level 'transcription_ru'; same Cyrillic-only and U+0301 stress rules. Omit if same as default. "
            .'Return only one translation per sense. Return ONLY valid JSON, with no extra commentary or code fences.';

        $payload = [
            'model' => 'gpt-5.4-mini',
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
