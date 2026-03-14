<?php

namespace App\Features\Flashcards\Http\Controllers;

use App\Features\Flashcards\Models\HebrewForm;
use App\Features\Flashcards\Models\Language;
use App\Features\Flashcards\Models\Shoresh;
use App\Features\Flashcards\Models\Translation;
use App\Features\Flashcards\Services\WordImport\GeminiWordImportSource;
use App\Features\Flashcards\Http\Requests\StoreHebrewFormRequest;
use App\Features\Flashcards\Http\Requests\UpdateHebrewFormRequest;
use App\Helpers\TenancyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WordController
{
    protected function ensureDefaultDeck()
    {
        $user = Auth::user();
        $deck = $user->decks()->where('is_default', true)->first();

        if (!$deck) {
            $deck = $user->decks()->create([
                'name' => 'My deck',
                'slug' => 'default',
                'is_default' => true,
            ]);
        }

        return $deck;
    }

    protected function syncTranslations(HebrewForm $form, array $translationIds, array $newRu = [], array $newEntries = []): void
    {
        $lang = Language::where('code', 'ru')->first();

        $syncData = [];

        // Existing translations selected from the multiselect (no extra pivot data)
        foreach (collect($translationIds)->filter() as $id) {
            $syncData[$id] = [];
        }

        // Structured new entries: translation + form_type (+ implied sense order)
        if ($lang && !empty($newEntries)) {
            foreach ($newEntries as $index => $entry) {
                $text = trim((string) ($entry['translation_ru'] ?? ''));
                if ($text === '') {
                    continue;
                }

                $t = Translation::firstOrCreate(
                    ['language_id' => $lang->id, 'text' => $text],
                    ['language_id' => $lang->id, 'text' => $text]
                );

                $formType = isset($entry['form_type']) && trim((string) $entry['form_type']) !== ''
                    ? trim((string) $entry['form_type'])
                    : null;

                $syncData[$t->id] = [
                    'form_type' => $formType,
                    'sense_order' => $index + 1,
                ];
            }
        }

        // Legacy support: plain list of new RU translations (no per-sense form type)
        if ($lang && !empty($newRu)) {
            foreach ($newRu as $text) {
                $text = trim((string) $text);
                if ($text === '') {
                    continue;
                }

                $t = Translation::firstOrCreate(
                    ['language_id' => $lang->id, 'text' => $text],
                    ['language_id' => $lang->id, 'text' => $text]
                );

                if (!array_key_exists($t->id, $syncData)) {
                    $syncData[$t->id] = [];
                }
            }
        }

        if (empty($syncData)) {
            $form->translations()->sync([]);
        } else {
            $form->translations()->sync($syncData);
        }
    }

    public function index(Request $request)
    {
        $query = HebrewForm::with(['shoresh', 'translations.language']);

        if ($request->filled('shoresh_id')) {
            $query->where('shoresh_id', $request->shoresh_id);
        }

        if ($request->filled('language')) {
            $lang = Language::where('code', $request->language)->first();
            if ($lang) {
                $query->whereHas('translations', fn ($q) => $q->where('language_id', $lang->id));
            }
        }

        $words = $query->orderBy('form_text')->paginate(20);
        $shoreshim = Shoresh::orderBy('root')->get();

        return TenancyHelper::view('flashcards.words.index', [
            'words' => $words,
            'shoreshim' => $shoreshim,
        ]);
    }

    public function create()
    {
        $shoreshim = Shoresh::orderBy('root')->get();
        $translationsRu = Translation::whereHas('language', fn ($q) => $q->where('code', 'ru'))->orderBy('text')->get();

        return TenancyHelper::view('flashcards.words.create', [
            'shoreshim' => $shoreshim,
            'translationsRu' => $translationsRu,
        ]);
    }

    public function store(StoreHebrewFormRequest $request)
    {
        $shoreshId = $request->shoresh_id;
        if ($request->filled('new_shoresh')) {
            $shoresh = Shoresh::firstOrCreate(['root' => trim($request->new_shoresh)]);
            $shoreshId = $shoresh->id;
        }

        $form = HebrewForm::create([
            'shoresh_id' => $shoreshId,
            'form_text' => $request->form_text,
            'form_type' => $request->form_type,
            'transcription_ru' => $request->transcription_ru,
            'frequency_rank' => $request->frequency_rank,
            'frequency_per_million' => $request->frequency_per_million,
        ]);

        $newRu = is_array($request->new_translations_ru) ? $request->new_translations_ru : array_filter(array_map('trim', explode("\n", (string) $request->new_translations_ru)));
        $newEntries = $request->input('new_entries', []);

        $this->syncTranslations(
            $form,
            $request->translation_ids ?? [],
            $newRu,
            $newEntries
        );

        if ($request->boolean('add_to_deck')) {
            $deck = $this->ensureDefaultDeck();
            $deck->deckCards()->firstOrCreate(['hebrew_form_id' => $form->id]);
        }

        return redirect()->route('flashcards.words.index')
            ->with('success', 'Word added.');
    }

    public function edit(HebrewForm $hebrewForm)
    {
        $shoreshim = Shoresh::orderBy('root')->get();
        $translationsRu = Translation::whereHas('language', fn ($q) => $q->where('code', 'ru'))->orderBy('text')->get();

        return TenancyHelper::view('flashcards.words.edit', [
            'word' => $hebrewForm,
            'shoreshim' => $shoreshim,
            'translationsRu' => $translationsRu,
        ]);
    }

    public function update(UpdateHebrewFormRequest $request, HebrewForm $hebrewForm)
    {
        $shoreshId = $request->shoresh_id;
        if ($request->filled('new_shoresh')) {
            $shoresh = Shoresh::firstOrCreate(['root' => trim($request->new_shoresh)]);
            $shoreshId = $shoresh->id;
        }

        $hebrewForm->update([
            'shoresh_id' => $shoreshId,
            'form_text' => $request->form_text,
            'form_type' => $request->form_type,
            'transcription_ru' => $request->transcription_ru,
            'frequency_rank' => $request->frequency_rank,
            'frequency_per_million' => $request->frequency_per_million,
        ]);

        $newRu = is_array($request->new_translations_ru) ? $request->new_translations_ru : array_filter(array_map('trim', explode("\n", (string) $request->new_translations_ru)));
        $newEntries = $request->input('new_entries', []);

        $this->syncTranslations(
            $hebrewForm,
            $request->translation_ids ?? [],
            $newRu,
            $newEntries
        );

        return redirect()->route('flashcards.words.index')
            ->with('success', 'Word updated.');
    }

    public function destroy(HebrewForm $hebrewForm)
    {
        $hebrewForm->delete();
        return redirect()->route('flashcards.words.index')
            ->with('success', 'Word deleted.');
    }

    public function addToDeck(HebrewForm $hebrewForm)
    {
        $deck = $this->ensureDefaultDeck();
        $deck->deckCards()->firstOrCreate(['hebrew_form_id' => $hebrewForm->id]);

        return redirect()->back()
            ->with('success', 'Added to your deck.');
    }

    public function import(Request $request)
    {
        $word = trim((string) $request->query('word'));
        $sourceKey = $request->query('source');

        if ($word === '') {
            return response()->json(['error' => 'Word is required'], 400, [], JSON_UNESCAPED_UNICODE);
        }

        $sources = [
            'gemini' => app(GeminiWordImportSource::class),
        ];

        $source = $sources[$sourceKey] ?? null;
        if (!$source) {
            return response()->json(['error' => 'No import source configured'], 400, [], JSON_UNESCAPED_UNICODE);
        }

        $data = $source->fetch($word);
        if ($data === null) {
            return response()->json(['error' => 'No data found'], 404, [], JSON_UNESCAPED_UNICODE);
        }
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
