{{--
  Single sense block: stacked fields for small screens (used by word-form-fields).
  Expects: $idx (int), $inputClass, $btnStressSmall, and optional value vars.
--}}
@php
    $translationRu = $translationRu ?? '';
    $formType = $formType ?? '';
    $transcriptionRu = $transcriptionRu ?? '';
@endphp
<div class="entry-row relative border border-gray-200 rounded-xl p-4 space-y-3 bg-gray-50/80 shadow-sm">
    <div class="flex items-center justify-between gap-2">
        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Sense</span>
        <button type="button" class="entry-delete min-h-[2.5rem] min-w-[2.5rem] inline-flex items-center justify-center text-lg leading-none text-red-600 hover:bg-red-50 rounded-lg active:bg-red-100" title="Remove sense">×</button>
    </div>
    <div class="space-y-1">
        <label class="block text-xs font-medium text-gray-600">Translation (RU)</label>
        <input type="text"
               name="new_entries[{{ $idx }}][translation_ru]"
               value="{{ $translationRu }}"
               class="w-full {{ $inputClass }}"
               placeholder="Translation (RU)"
               autocomplete="off">
    </div>
    <div class="space-y-1">
        <label class="block text-xs font-medium text-gray-600">Form type</label>
        <input type="text"
               name="new_entries[{{ $idx }}][form_type]"
               value="{{ $formType }}"
               class="w-full {{ $inputClass }}"
               placeholder="e.g. noun (masc.)"
               autocomplete="off">
    </div>
    <div class="space-y-1">
        <label class="block text-xs font-medium text-gray-600">Transcription if different</label>
        <div class="flex gap-2 items-stretch">
            <input type="text"
                   name="new_entries[{{ $idx }}][transcription_ru]"
                   value="{{ $transcriptionRu }}"
                   class="flex-1 min-w-0 {{ $inputClass }}"
                   placeholder="Leave empty to use default above"
                   autocomplete="off">
            <button type="button" class="entry-transcription-stress shrink-0 {{ $btnStressSmall }}" title="Cycle stress to next vowel (left to right)">Stress</button>
        </div>
    </div>
</div>
