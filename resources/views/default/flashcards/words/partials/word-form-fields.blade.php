{{-- Requires WordFormTheme::variables() extract() earlier in the same view (sets $inputClass, etc.). --}}
@php
    $word = $word ?? null;
    $formTextReadonly = $formTextReadonly ?? false;
    $geminiImportLabel = 'G';
    $openaiImportLabel = 'O';
    $formTextClass = trim('flex-1 ' . $inputClassLg . ($formTextReadonly ? ' bg-gray-50' : ''));
@endphp

<div>
    <label class="block font-medium text-gray-700 mb-1">Hebrew form *</label>
    <div class="flex gap-2">
        <input type="text" name="form_text" id="form_text"
               value="{{ old('form_text', $word?->form_text) }}"
               required dir="rtl"
               @if($formTextReadonly) readonly @endif
               class="{{ $formTextClass }}">
        <button type="button"
                id="gemini-import-btn"
                class="{{ $btnPrimary }} disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none">
            {{ $geminiImportLabel }}
        </button>
        <button type="button"
                id="openai-import-btn"
                class="{{ $btnPrimary }} disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none">
            {{ $openaiImportLabel }}
        </button>
    </div>
</div>

<div>
    <label class="block font-medium text-gray-700 mb-1">Root</label>
    <input type="text" name="shoresh_root" id="shoresh_root" value="{{ old('shoresh_root', $word?->shoresh?->root ?? '') }}" placeholder="e.g. שלמ" dir="rtl" class="{{ $inputClass }}">
    <p class="text-xs text-gray-500 mt-1"></p>
</div>

<div>
    <label class="block font-medium text-gray-700 mb-1">Default Transcription</label>
    <p class="text-xs text-gray-500 mb-1"></p>
    <div class="flex gap-2">
        <input type="text" name="transcription_ru" id="transcription_ru" value="{{ old('transcription_ru', $word?->transcription_ru) }}" class="flex-1 {{ $inputClass }}">
        <button type="button" id="transcription_ru_cycle_stress" class="{{ $btnStress }}" title="Cycle stress to next vowel (left to right)">Stress</button>
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block font-medium text-gray-700 mb-1">Frequency rank</label>
        <input type="number" name="frequency_rank" id="frequency_rank" value="{{ old('frequency_rank', $word?->frequency_rank) }}" min="1" class="{{ $inputClass }}">
    </div>
    <div>
        <label class="block font-medium text-gray-700 mb-1">Per million</label>
        <input type="number" name="frequency_per_million" id="frequency_per_million" value="{{ old('frequency_per_million', $word?->frequency_per_million) }}" step="0.01" min="0" class="{{ $inputClass }}">
    </div>
</div>

<div>
    <label class="block font-medium text-gray-700 mb-1">Translations (Russian)</label>
    <p class="text-sm text-gray-500 mb-1"></p>
    @if ($word)
        @php
            $oldEntries = old('new_entries');
        @endphp
        <div>
            <div class="flex items-center justify-between mb-1">
                <span class="text-sm font-medium text-gray-700">Senses</span>
                <button type="button" id="add-entry-row" class="text-sm text-indigo-600 hover:underline">+ Add sense</button>
            </div>
            <div id="entries-container" class="space-y-3">
                @if (is_array($oldEntries))
                    @foreach ($oldEntries as $idx => $entry)
                        @include('default.flashcards.words.partials.word-form-sense-row', [
                            'idx' => $idx,
                            'translationRu' => $entry['translation_ru'] ?? '',
                            'formType' => $entry['form_type'] ?? '',
                            'transcriptionRu' => $entry['transcription_ru'] ?? '',
                        ])
                    @endforeach
                @elseif($word->translations && $word->translations->count())
                    @foreach ($word->translations as $idx => $t)
                        @include('default.flashcards.words.partials.word-form-sense-row', [
                            'idx' => $idx,
                            'translationRu' => $t->text,
                            'formType' => $t->pivot->form_type ?? '',
                            'transcriptionRu' => $t->pivot->transcription_ru ?? '',
                        ])
                    @endforeach
                @else
                    @include('default.flashcards.words.partials.word-form-sense-row', [
                        'idx' => 0,
                        'translationRu' => '',
                        'formType' => '',
                        'transcriptionRu' => '',
                    ])
                @endif
            </div>
        </div>
    @else
        @php
            $oldEntries = old('new_entries', []);
        @endphp
        <div>
            <div class="flex items-center justify-between mb-1">
                <span class="text-sm font-medium text-gray-700">Senses</span>
                <button type="button" id="add-entry-row" class="text-sm text-indigo-600 hover:underline">+ Add sense</button>
            </div>
            <div id="entries-container" class="space-y-3">
                @forelse ($oldEntries as $idx => $entry)
                    @include('default.flashcards.words.partials.word-form-sense-row', [
                        'idx' => $idx,
                        'translationRu' => $entry['translation_ru'] ?? '',
                        'formType' => $entry['form_type'] ?? '',
                        'transcriptionRu' => $entry['transcription_ru'] ?? '',
                    ])
                @empty
                    @include('default.flashcards.words.partials.word-form-sense-row', [
                        'idx' => 0,
                        'translationRu' => '',
                        'formType' => '',
                        'transcriptionRu' => '',
                    ])
                @endforelse
            </div>
        </div>
    @endif
</div>
