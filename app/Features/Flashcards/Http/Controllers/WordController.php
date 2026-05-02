<?php

namespace App\Features\Flashcards\Http\Controllers;

use App\Features\Flashcards\Http\Requests\BulkQueueHebrewWordsRequest;
use App\Features\Flashcards\Http\Requests\ImportExtraSenseRequest;
use App\Features\Flashcards\Http\Requests\StoreHebrewFormRequest;
use App\Features\Flashcards\Http\Requests\UpdateHebrewFormRequest;
use App\Features\Flashcards\Models\HebrewForm;
use App\Features\Flashcards\Models\Language;
use App\Features\Flashcards\Models\Shoresh;
use App\Features\Flashcards\Models\Translation;
use App\Features\Flashcards\Services\BulkWordLineParser;
use App\Features\Flashcards\Services\TranscriptionRuNormalizer;
use App\Features\Flashcards\Services\SenseImport\DatabaseExtraSenseSource;
use App\Features\Flashcards\Services\SenseImport\GeminiExtraSenseSource;
use App\Features\Flashcards\Services\SenseImport\OpenAiExtraSenseSource;
use App\Features\Flashcards\Services\WordImport\DBWordImportSource;
use App\Features\Flashcards\Services\WordImport\GeminiWordImportSource;
use App\Features\Flashcards\Services\WordImport\OpenAiWordImportSource;
use App\Features\Flashcards\Services\WordImport\UnitedWordImportSource;
use App\Helpers\TenancyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WordController
{
    protected function ensureDefaultDeck()
    {
        $user = Auth::user();
        $deck = $user->decks()->where('is_default', true)->first();

        if (! $deck) {
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
        if ($lang && ! empty($newEntries)) {
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

                $pivotTranscription = null;
                $overrideRaw = trim((string) ($entry['transcription_ru'] ?? ''));
                if ($overrideRaw !== '') {
                    $normalized = TranscriptionRuNormalizer::normalize($overrideRaw);
                    $pivotTranscription = ($normalized === '' || $normalized === null) ? null : $normalized;
                }

                $syncData[$t->id] = [
                    'form_type' => $formType,
                    'sense_order' => $index + 1,
                    'transcription_ru' => $pivotTranscription,
                ];
            }
        }

        // Legacy support: plain list of new RU translations (no per-sense form type)
        if ($lang && ! empty($newRu)) {
            foreach ($newRu as $text) {
                $text = trim((string) $text);
                if ($text === '') {
                    continue;
                }

                $t = Translation::firstOrCreate(
                    ['language_id' => $lang->id, 'text' => $text],
                    ['language_id' => $lang->id, 'text' => $text]
                );

                if (! array_key_exists($t->id, $syncData)) {
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

        $defaultDeck = Auth::user()->decks()->where('is_default', true)->first();
        $wordIdsOnPage = $words->pluck('id')->all();
        $inDeckHebrewFormIds = [];
        if ($defaultDeck !== null && $wordIdsOnPage !== []) {
            $ids = $defaultDeck->deckCards()
                ->whereIn('hebrew_form_id', $wordIdsOnPage)
                ->pluck('hebrew_form_id')
                ->all();
            $inDeckHebrewFormIds = array_fill_keys($ids, true);
        }

        return TenancyHelper::view('flashcards.words.index', [
            'words' => $words,
            'shoreshim' => $shoreshim,
            'defaultDeck' => $defaultDeck,
            'inDeckHebrewFormIds' => $inDeckHebrewFormIds,
        ]);
    }

    public function create()
    {
        return TenancyHelper::view('flashcards.words.create');
    }

    public function bulkCreate()
    {
        return TenancyHelper::view('flashcards.words.bulk-create');
    }

    public function bulkQueue(BulkQueueHebrewWordsRequest $request)
    {
        $unique = BulkWordLineParser::uniqueLines((string) $request->input('lines'));
        $added = 0;
        $skipped = 0;

        foreach ($unique as $w) {
            if (HebrewForm::where('form_text', $w)->exists()) {
                $skipped++;

                continue;
            }

            HebrewForm::create([
                'shoresh_id' => null,
                'form_text' => $w,
                'transcription_ru' => null,
                'frequency_rank' => null,
                'frequency_per_million' => null,
            ]);
            $added++;
        }

        if ($added === 0) {
            $msg = 'No new words to add.';
            if ($skipped > 0) {
                $msg .= ' ('.$skipped.' already in the list.)';
            }

            return redirect()->route('flashcards.dashboard')
                ->with('info', $msg);
        }

        $msg = 'Saved '.$added.' word(s) to the database.';
        if ($skipped > 0) {
            $msg .= ' Skipped '.$skipped.' already in the list.';
        }
        $msg .= ' Use Process new words on the dashboard to add Russian translations and other details.';

        return redirect()->route('flashcards.dashboard')
            ->with('success', $msg);
    }

    public function processPendingWords(Request $request)
    {
        $pendingTotal = HebrewForm::pendingEnrichment()->count();
        if ($pendingTotal === 0) {
            return redirect()->route('flashcards.dashboard')
                ->with('info', 'No words missing Russian translations.');
        }

        $word = HebrewForm::pendingEnrichment()
            ->orderBy('id')
            ->with([
                'shoresh',
                'translations' => function ($q) {
                    $q->orderByPivot('sense_order');
                },
                'translations.language',
            ])
            ->firstOrFail();

        $pendingPosition = HebrewForm::pendingEnrichment()
            ->where('id', '<', $word->id)
            ->count() + 1;

        return TenancyHelper::view('flashcards.words.process-pending', [
            'word' => $word,
            'pendingPosition' => $pendingPosition,
            'pendingTotal' => $pendingTotal,
        ]);
    }

    public function store(StoreHebrewFormRequest $request)
    {
        $shoreshId = null;
        $root = trim((string) $request->input('shoresh_root', ''));
        if ($root !== '') {
            $shoresh = Shoresh::firstOrCreate(['root' => $root]);
            $shoreshId = $shoresh->id;
        }

        $form = HebrewForm::create([
            'shoresh_id' => $shoreshId,
            'form_text' => $request->form_text,
            'transcription_ru' => TranscriptionRuNormalizer::normalize($request->transcription_ru),
            'frequency_rank' => $request->frequency_rank,
            'frequency_per_million' => $request->frequency_per_million,
        ]);

        $newEntries = $request->input('new_entries', []);

        $this->syncTranslations($form, [], [], $newEntries);

        if ($request->boolean('add_to_deck')) {
            $deck = $this->ensureDefaultDeck();
            $deck->deckCards()->firstOrCreate(['hebrew_form_id' => $form->id]);
        }

        if ($request->boolean('save_continue')) {
            return redirect()->route('flashcards.words.create')
                ->with('success', 'Word added.');
        }

        return redirect()->route('flashcards.words.index')
            ->with('success', 'Word added.');
    }

    public function edit(HebrewForm $hebrewForm)
    {
        $hebrewForm->load([
            'shoresh',
            'translations' => function ($q) {
                $q->orderByPivot('sense_order');
            },
            'translations.language',
        ]);

        return TenancyHelper::view('flashcards.words.edit', [
            'word' => $hebrewForm,
        ]);
    }

    public function update(UpdateHebrewFormRequest $request, HebrewForm $hebrewForm)
    {
        $shoreshId = null;
        $root = trim((string) $request->input('shoresh_root', ''));
        if ($root !== '') {
            $shoresh = Shoresh::firstOrCreate(['root' => $root]);
            $shoreshId = $shoresh->id;
        }

        $hebrewForm->update([
            'shoresh_id' => $shoreshId,
            'form_text' => $request->form_text,
            'transcription_ru' => TranscriptionRuNormalizer::normalize($request->transcription_ru),
            'frequency_rank' => $request->frequency_rank,
            'frequency_per_million' => $request->frequency_per_million,
        ]);

        $newEntries = $request->input('new_entries', []);

        $this->syncTranslations($hebrewForm, [], [], $newEntries);

        $deck = $this->ensureDefaultDeck();
        if ($request->boolean('add_to_deck')) {
            $deck->deckCards()->firstOrCreate(['hebrew_form_id' => $hebrewForm->id]);
        } else {
            $deck->deckCards()->where('hebrew_form_id', $hebrewForm->id)->delete();
        }

        if ($request->boolean('enrichment_flow')) {
            if (HebrewForm::pendingEnrichment()->exists()) {
                return redirect()->route('flashcards.words.process-pending')
                    ->with('success', 'Saved. Next word.');
            }

            return redirect()->route('flashcards.dashboard')
                ->with('success', 'All words now have at least one Russian translation.');
        }

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
            return response()->json([
                'error' => 'Word is required',
                'code' => 'WORD_REQUIRED',
                'source' => $sourceKey,
            ], 400, [], JSON_UNESCAPED_UNICODE);
        }

        $sources = [
            'db' => app(DBWordImportSource::class),
            'gemini' => app(GeminiWordImportSource::class),
            'openai' => app(OpenAiWordImportSource::class),
            'united' => app(UnitedWordImportSource::class),
        ];

        $source = $sources[$sourceKey] ?? null;
        if (! $source) {
            return response()->json([
                'error' => 'No import source configured',
                'code' => 'UNKNOWN_SOURCE',
                'source' => $sourceKey,
            ], 400, [], JSON_UNESCAPED_UNICODE);
        }

        $data = $source->fetch($word);
        if ($data === null) {
            $isDb = $sourceKey === 'db';

            return response()->json([
                'error' => $isDb
                    ? 'No word saved with this exact form.'
                    : 'No data found.',
                'code' => $isDb ? 'WORD_NOT_IN_DATABASE' : 'IMPORT_EMPTY',
                'source' => $sourceKey,
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function importExtraSense(ImportExtraSenseRequest $request)
    {
        $formText = trim((string) $request->input('form_text'));
        $sourceKey = (string) $request->input('source');
        /** @var array<int, string|null> $existing */
        $existing = $request->input('existing_translations', []);

        $sources = [
            'db' => app(DatabaseExtraSenseSource::class),
            'gemini' => app(GeminiExtraSenseSource::class),
            'openai' => app(OpenAiExtraSenseSource::class),
        ];

        $source = $sources[$sourceKey] ?? null;
        if (! $source) {
            return response()->json([
                'error' => 'No import source configured',
                'code' => 'UNKNOWN_SOURCE',
                'source' => $sourceKey,
            ], 400, [], JSON_UNESCAPED_UNICODE);
        }

        if ($sourceKey === 'db' && ! HebrewForm::where('form_text', $formText)->exists()) {
            return response()->json([
                'error' => 'No word saved with this exact form.',
                'code' => 'WORD_NOT_IN_DATABASE',
                'source' => $sourceKey,
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $entry = $source->fetchOne($formText, $existing);
        if ($entry === null) {
            return response()->json([
                'error' => $sourceKey === 'db'
                    ? 'No additional sense in the database for this form.'
                    : 'No additional sense found.',
                'code' => $sourceKey === 'db' ? 'NO_EXTRA_DB_SENSE' : 'NO_EXTRA_SENSE',
                'source' => $sourceKey,
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json(['entry' => $entry], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
