{{-- Requires WordFormTheme::variables() extract() earlier in the same view (sets $inputClass, etc.). --}}
@php
    $word = $word ?? null;
    $formTextReadonly = $formTextReadonly ?? false;
    $geminiImportLabel = $geminiImportLabel ?? ($word ? 'Request from Gemini' : 'Import from Gemini');
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
                class="{{ $btnPrimary }}">
            {{ $geminiImportLabel }}
        </button>
    </div>
    <p class="text-xs text-gray-500 mt-1" id="gemini-import-status"></p>
</div>

<div>
    <label class="block font-medium text-gray-700 mb-1">Root (shoresh)</label>
    <input type="text" name="shoresh_root" id="shoresh_root" value="{{ old('shoresh_root', $word?->shoresh?->root ?? '') }}" placeholder="e.g. שלמ" dir="rtl" class="{{ $inputClass }}">
    <p class="text-xs text-gray-500 mt-1">Links existing root or creates if missing.</p>
</div>

<div>
    <label class="block font-medium text-gray-700 mb-1">Transcription (Russian)</label>
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
        <label class="block font-medium text-gray-700 mb-1">Frequency per million</label>
        <input type="number" name="frequency_per_million" id="frequency_per_million" value="{{ old('frequency_per_million', $word?->frequency_per_million) }}" step="0.01" min="0" class="{{ $inputClass }}">
    </div>
</div>

<div>
    <label class="block font-medium text-gray-700 mb-1">Translations (Russian)</label>
    <p class="text-sm text-gray-500 mb-1">Each sense: translation + form type. Links existing or creates if missing.</p>
    @if ($word)
        @php
            $oldEntries = old('new_entries');
        @endphp
        <div>
            <div class="flex items-center justify-between mb-1">
                <span class="text-sm font-medium text-gray-700">Senses</span>
                <button type="button" id="add-entry-row" class="text-sm text-indigo-600 hover:underline">+ Add sense</button>
            </div>
            <div id="entries-container" class="space-y-2">
                @if (is_array($oldEntries))
                    @foreach ($oldEntries as $idx => $entry)
                        <div class="grid grid-cols-[1fr_1fr_auto] gap-2 entry-row items-center">
                            <input type="text"
                                   name="new_entries[{{ $idx }}][translation_ru]"
                                   value="{{ $entry['translation_ru'] ?? '' }}"
                                   class="{{ $inputClass }}"
                                   placeholder="Translation (RU)">
                            <input type="text"
                                   name="new_entries[{{ $idx }}][form_type]"
                                   value="{{ $entry['form_type'] ?? '' }}"
                                   class="{{ $inputClass }}"
                                   placeholder="Form type (e.g. noun (masc.))">
                            <button type="button" class="entry-delete px-2 py-1 text-red-600 hover:bg-red-50 rounded" title="Remove sense">×</button>
                        </div>
                    @endforeach
                @elseif($word->translations && $word->translations->count())
                    @foreach ($word->translations as $idx => $t)
                        <div class="grid grid-cols-[1fr_1fr_auto] gap-2 entry-row items-center">
                            <input type="text"
                                   name="new_entries[{{ $idx }}][translation_ru]"
                                   value="{{ $t->text }}"
                                   class="{{ $inputClass }}"
                                   placeholder="Translation (RU)">
                            <input type="text"
                                   name="new_entries[{{ $idx }}][form_type]"
                                   value="{{ $t->pivot->form_type ?? '' }}"
                                   class="{{ $inputClass }}"
                                   placeholder="Form type (e.g. noun (masc.))">
                            <button type="button" class="entry-delete px-2 py-1 text-red-600 hover:bg-red-50 rounded" title="Remove sense">×</button>
                        </div>
                    @endforeach
                @else
                    <div class="grid grid-cols-[1fr_1fr_auto] gap-2 entry-row items-center">
                        <input type="text"
                               name="new_entries[0][translation_ru]"
                               class="{{ $inputClass }}"
                               placeholder="Translation (RU)">
                        <input type="text"
                               name="new_entries[0][form_type]"
                               class="{{ $inputClass }}"
                               placeholder="Form type (e.g. noun (masc.))">
                        <button type="button" class="entry-delete px-2 py-1 text-red-600 hover:bg-red-50 rounded" title="Remove sense">×</button>
                    </div>
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
            <div id="entries-container" class="space-y-2">
                @forelse ($oldEntries as $idx => $entry)
                    <div class="grid grid-cols-[1fr_1fr_auto] gap-2 entry-row items-center">
                        <input type="text"
                               name="new_entries[{{ $idx }}][translation_ru]"
                               value="{{ $entry['translation_ru'] ?? '' }}"
                               class="{{ $inputClass }}"
                               placeholder="Translation (RU)">
                        <input type="text"
                               name="new_entries[{{ $idx }}][form_type]"
                               value="{{ $entry['form_type'] ?? '' }}"
                               class="{{ $inputClass }}"
                               placeholder="Form type (e.g. noun (masc.))">
                        <button type="button" class="entry-delete px-2 py-1 text-red-600 hover:bg-red-50 rounded" title="Remove sense">×</button>
                    </div>
                @empty
                    <div class="grid grid-cols-[1fr_1fr_auto] gap-2 entry-row items-center">
                        <input type="text"
                               name="new_entries[0][translation_ru]"
                               class="{{ $inputClass }}"
                               placeholder="Translation (RU)">
                        <input type="text"
                               name="new_entries[0][form_type]"
                               class="{{ $inputClass }}"
                               placeholder="Form type (e.g. noun (masc.))">
                        <button type="button" class="entry-delete px-2 py-1 text-red-600 hover:bg-red-50 rounded" title="Remove sense">×</button>
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
