{{-- Russian-first add word: gloss + import, then Hebrew form rows. Requires WordFormThemeFactory::variables(). --}}
@php
    $dbImportLabel = 'D';
    $geminiImportLabel = 'G';
    $oldRows = old('hebrew_forms', []);
    if (! is_array($oldRows)) {
        $oldRows = [];
    }
    $hasOldRows = false;
    foreach ($oldRows as $row) {
        if (is_array($row) && trim((string) ($row['form_text'] ?? '')) !== '') {
            $hasOldRows = true;
            break;
        }
    }
@endphp

<div>
    <label class="block font-medium text-gray-700 mb-1">Russian word <span class="text-red-600">*</span></label>
    <p class="text-xs text-gray-500 mb-1">Type the Russian gloss, then use D/G to import Hebrew forms.</p>
    <div class="flex gap-2">
        <input type="text"
               name="russian_gloss"
               id="russian_gloss"
               value="{{ old('russian_gloss') }}"
               required
               dir="ltr"
               lang="ru"
               class="flex-1 {{ $inputClassLg }}"
               autocomplete="off">
        <button type="button"
                id="db-import-btn"
                title="Import from database (exact Russian translation match)"
                class="{{ $btnPrimary }} disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none">
            {{ $dbImportLabel }}
        </button>
        <button type="button"
                id="gemini-import-btn"
                class="{{ $btnPrimary }} disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none">
            {{ $geminiImportLabel }}
        </button>
    </div>
</div>

<div>
    <div class="flex items-center justify-between mb-1">
        <span class="text-sm font-medium text-gray-700">Hebrew forms</span>
        <button type="button" id="add-hebrew-form-row" class="text-sm text-indigo-600 hover:underline">+ Add Hebrew form</button>
    </div>
    @include(\App\Helpers\TenancyHelper::getViewPath('flashcards.words.partials.form-type-datalist'))
    <div id="entries-container" class="space-y-3">
        @if ($hasOldRows)
            @foreach ($oldRows as $idx => $row)
                @if (is_array($row) && trim((string) ($row['form_text'] ?? '')) !== '')
                    @include(\App\Helpers\TenancyHelper::getViewPath('flashcards.words.partials.word-form-hebrew-row'), [
                        'idx' => $idx,
                        'formText' => $row['form_text'] ?? '',
                        'shoreshRoot' => $row['shoresh_root'] ?? '',
                        'transcriptionRu' => $row['transcription_ru'] ?? '',
                        'frequencyRank' => $row['frequency_rank'] ?? '',
                        'frequencyPerMillion' => $row['frequency_per_million'] ?? '',
                        'formType' => $row['form_type'] ?? '',
                        'addToDeck' => ($row['add_to_deck'] ?? '1') == '1',
                    ])
                @endif
            @endforeach
        @else
            @include(\App\Helpers\TenancyHelper::getViewPath('flashcards.words.partials.word-form-hebrew-row'), [
                'idx' => 0,
                'formText' => '',
                'shoreshRoot' => '',
                'transcriptionRu' => '',
                'frequencyRank' => '',
                'frequencyPerMillion' => '',
                'formType' => '',
                'addToDeck' => true,
            ])
        @endif
    </div>
</div>
