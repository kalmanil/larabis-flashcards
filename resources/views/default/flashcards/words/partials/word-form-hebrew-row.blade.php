{{-- One Hebrew lemma row on «Добавить слово» (Russian-first add). Expects $idx and WordFormThemeFactory variables. --}}
@php
    $formText = $formText ?? '';
    $shoreshRoot = $shoreshRoot ?? '';
    $transcriptionRu = $transcriptionRu ?? '';
    $frequencyRank = $frequencyRank ?? '';
    $frequencyPerMillion = $frequencyPerMillion ?? '';
    $formType = $formType ?? '';
    $addToDeck = $addToDeck ?? true;
@endphp
<div class="hebrew-form-row entry-row relative border border-gray-200 rounded-xl p-4 space-y-3 bg-gray-50/80 shadow-sm">
    <div class="flex items-center justify-between gap-2 flex-wrap">
        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Hebrew form</span>
        <button type="button"
                class="hebrew-form-row-delete min-h-[2.5rem] min-w-[2.5rem] inline-flex items-center justify-center text-lg leading-none text-red-600 hover:bg-red-50 rounded-lg active:bg-red-100"
                title="Remove Hebrew form">×</button>
    </div>
    <div class="space-y-1">
        <label class="block text-xs font-medium text-gray-600">Hebrew form <span class="text-red-600">*</span></label>
        <input type="text"
               name="hebrew_forms[{{ $idx }}][form_text]"
               value="{{ $formText }}"
               required
               dir="rtl"
               class="w-full {{ $inputClassLg }}"
               autocomplete="off">
    </div>
    <div class="space-y-1">
        <label class="block text-xs font-medium text-gray-600">Root</label>
        <input type="text"
               name="hebrew_forms[{{ $idx }}][shoresh_root]"
               value="{{ $shoreshRoot }}"
               dir="rtl"
               placeholder="e.g. שלמ"
               class="w-full {{ $inputClass }}"
               autocomplete="off">
    </div>
    <div class="space-y-1">
        <label class="block text-xs font-medium text-gray-600">Form type</label>
        <input type="text"
               name="hebrew_forms[{{ $idx }}][form_type]"
               value="{{ $formType }}"
               class="w-full {{ $inputClass }}"
               list="flashcards-form-type-options"
               placeholder="Choose a suggestion or type (aliases OK)"
               autocomplete="off">
    </div>
    <div class="space-y-1">
        <label class="block text-xs font-medium text-gray-600">Transcription</label>
        <div class="flex gap-2 items-stretch">
            <input type="text"
                   name="hebrew_forms[{{ $idx }}][transcription_ru]"
                   value="{{ $transcriptionRu }}"
                   class="flex-1 min-w-0 {{ $inputClass }}"
                   autocomplete="off">
            <button type="button"
                    class="hebrew-form-row-transcription-stress shrink-0 {{ $btnStressSmall }}"
                    title="Cycle stress to next vowel (left to right)">Stress</button>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
            <label class="block text-xs font-medium text-gray-600">Frequency rank</label>
            <input type="number"
                   name="hebrew_forms[{{ $idx }}][frequency_rank]"
                   value="{{ $frequencyRank }}"
                   min="1"
                   class="w-full {{ $inputClass }}"
                   autocomplete="off">
        </div>
        <div class="space-y-1">
            <label class="block text-xs font-medium text-gray-600">Per million</label>
            <input type="number"
                   name="hebrew_forms[{{ $idx }}][frequency_per_million]"
                   value="{{ $frequencyPerMillion }}"
                   step="0.01"
                   min="0"
                   class="w-full {{ $inputClass }}"
                   autocomplete="off">
        </div>
    </div>
    <div>
        <input type="hidden" name="hebrew_forms[{{ $idx }}][add_to_deck]" value="0">
        <label class="inline-flex items-center text-sm text-gray-700">
            <input type="checkbox"
                   name="hebrew_forms[{{ $idx }}][add_to_deck]"
                   value="1"
                   @checked($addToDeck)
                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="ml-2">Add to my deck</span>
        </label>
    </div>
</div>
