@extends('layouts.app')

@section('title', 'Edit Word - Flashcards')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
    @include('default.partials.nav')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Edit Word</h1>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('flashcards.words.update', $word) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Hebrew form *</label>
                        <input type="text" name="form_text" id="form_text" value="{{ old('form_text', $word->form_text) }}" required dir="rtl"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Root (shoresh)</label>
                        <select name="shoresh_id" id="shoresh_id" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">— None —</option>
                            @foreach ($shoreshim as $s)
                                <option value="{{ $s->id }}" {{ old('shoresh_id', $word->shoresh_id) == $s->id ? 'selected' : '' }}>{{ $s->root }}</option>
                            @endforeach
                        </select>
                        <p class="text-sm text-gray-500 mt-1">Or add new:</p>
                        <input type="text" name="new_shoresh" id="new_shoresh" value="{{ old('new_shoresh') }}" placeholder="e.g. כ־ת־ב" dir="rtl" class="w-full border border-gray-200 rounded-xl px-3 py-2 mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Form type (e.g. binyan)</label>
                        <input type="text" name="form_type" id="form_type" value="{{ old('form_type', $word->form_type) }}" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Transcription (Russian)</label>
                        <input type="text" name="transcription_ru" id="transcription_ru" value="{{ old('transcription_ru', $word->transcription_ru) }}" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Frequency rank</label>
                            <input type="number" name="frequency_rank" id="frequency_rank" value="{{ old('frequency_rank', $word->frequency_rank) }}" min="1" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Frequency per million</label>
                            <input type="number" name="frequency_per_million" id="frequency_per_million" value="{{ old('frequency_per_million', $word->frequency_per_million) }}" step="0.01" min="0" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1">Translations (Russian)</label>
                        <p class="text-sm text-gray-500 mb-1">Select existing or add new senses below (each with its own form type)</p>
                        <select name="translation_ids[]" multiple class="w-full border border-gray-200 rounded-xl px-3 py-2 h-24 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach ($translationsRu as $t)
                                <option value="{{ $t->id }}" {{ in_array($t->id, old('translation_ids', $word->translations->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $t->text }}</option>
                            @endforeach
                        </select>
                        @php
                            $oldEntries = old('new_entries');
                        @endphp
                        <div class="mt-3">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700">Senses for this word</span>
                                <button type="button" id="add-entry-row" class="text-sm text-indigo-600 hover:underline">+ Add sense</button>
                            </div>
                            <div id="entries-container" class="space-y-2">
                                @if (is_array($oldEntries))
                                    @foreach ($oldEntries as $idx => $entry)
                                        <div class="grid grid-cols-2 gap-2 entry-row">
                                            <input type="text"
                                                   name="new_entries[{{ $idx }}][translation_ru]"
                                                   value="{{ $entry['translation_ru'] ?? '' }}"
                                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                   placeholder="Translation (RU)">
                                            <input type="text"
                                                   name="new_entries[{{ $idx }}][form_type]"
                                                   value="{{ $entry['form_type'] ?? '' }}"
                                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                   placeholder="Form type (e.g. noun (masc.))">
                                        </div>
                                    @endforeach
                                @elseif($word->translations && $word->translations->count())
                                    @foreach ($word->translations as $idx => $t)
                                        <div class="grid grid-cols-2 gap-2 entry-row">
                                            <input type="text"
                                                   name="new_entries[{{ $idx }}][translation_ru]"
                                                   value="{{ $t->text }}"
                                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                   placeholder="Translation (RU)">
                                            <input type="text"
                                                   name="new_entries[{{ $idx }}][form_type]"
                                                   value="{{ $t->pivot->form_type ?? '' }}"
                                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                   placeholder="Form type (e.g. noun (masc.))">
                                        </div>
                                    @endforeach
                                @else
                                    <div class="grid grid-cols-2 gap-2 entry-row">
                                        <input type="text"
                                               name="new_entries[0][translation_ru]"
                                               class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                               placeholder="Translation (RU)">
                                        <input type="text"
                                               name="new_entries[0][form_type]"
                                               class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                               placeholder="Form type (e.g. noun (masc.))">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-2">
                        <button type="submit" class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 shadow-md transition-all duration-200">Update</button>
                        <a href="{{ route('flashcards.words.index') }}" class="px-6 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 font-medium transition-colors">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    (function () {
        const container = document.getElementById('entries-container');
        const addBtn = document.getElementById('add-entry-row');
        if (!container || !addBtn) {
            return;
        }
        function createEntryRow(index) {
            const row = document.createElement('div');
            row.className = 'grid grid-cols-2 gap-2 entry-row';
            row.innerHTML = '' +
                '<input type="text" name="new_entries[' + index + '][translation_ru]" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Translation (RU)">' +
                '<input type="text" name="new_entries[' + index + '][form_type]" class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Form type (e.g. noun (masc.))">';
            return row;
        }
        let index = container.querySelectorAll('.entry-row').length;
        addBtn.addEventListener('click', function () {
            const row = createEntryRow(index);
            container.appendChild(row);
            index++;
        });
    })();
</script>
@endsection
