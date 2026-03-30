@extends('layouts.app')

@section('title', 'Edit Word - Flashcards')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6">Edit Word</h1>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('flashcards.words.update', $word) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block font-medium text-gray-700 mb-1">Hebrew form *</label>
            <div class="flex gap-2">
                <input type="text" name="form_text" id="form_text" value="{{ old('form_text', $word->form_text) }}" required dir="rtl"
                       class="flex-1 border rounded px-3 py-2 text-lg">
                <button type="button" id="gemini-import-btn" class="px-3 py-2 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700">Request from Gemini</button>
            </div>
            <p class="text-xs text-gray-500 mt-1" id="gemini-import-status"></p>
        </div>

        <div>
            <label class="block font-medium text-gray-700 mb-1">Root (shoresh)</label>
            <input type="text" name="shoresh_root" id="shoresh_root" value="{{ old('shoresh_root', $word->shoresh?->root ?? '') }}" placeholder="e.g. שלמ" dir="rtl" class="w-full border rounded px-3 py-2">
            <p class="text-xs text-gray-500 mt-1">Links existing root or creates if missing.</p>
        </div>

        <div>
            <label class="block font-medium text-gray-700 mb-1">Transcription (Russian)</label>
            <p class="text-xs text-gray-500 mb-1">Default pronunciation for this form. Use per-sense overrides below only when a sense reads differently.</p>
            <div class="flex gap-2">
                <input type="text" name="transcription_ru" id="transcription_ru" value="{{ old('transcription_ru', $word->transcription_ru) }}" class="flex-1 border rounded px-3 py-2">
                <button type="button" id="transcription_ru_cycle_stress" class="px-3 py-2 text-sm bg-gray-200 text-gray-800 rounded hover:bg-gray-300" title="Cycle stress to next vowel (left to right)">Stress</button>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-medium text-gray-700 mb-1">Frequency rank</label>
                <input type="number" name="frequency_rank" id="frequency_rank" value="{{ old('frequency_rank', $word->frequency_rank) }}" min="1" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block font-medium text-gray-700 mb-1">Frequency per million</label>
                <input type="number" name="frequency_per_million" id="frequency_per_million" value="{{ old('frequency_per_million', $word->frequency_per_million) }}" step="0.01" min="0" class="w-full border rounded px-3 py-2">
            </div>
        </div>

        <div>
            <label class="block font-medium text-gray-700 mb-1">Translations (Russian)</label>
            <p class="text-sm text-gray-500 mb-1">Each sense is a separate block (translation, form type, optional transcription). Links existing or creates if missing.</p>
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
                            @include('admin.flashcards.words.partials.word-form-sense-row', [
                                'idx' => $idx,
                                'translationRu' => $entry['translation_ru'] ?? '',
                                'formType' => $entry['form_type'] ?? '',
                                'transcriptionRu' => $entry['transcription_ru'] ?? '',
                            ])
                        @endforeach
                    @elseif($word->translations && $word->translations->count())
                        @foreach ($word->translations as $idx => $t)
                            @include('admin.flashcards.words.partials.word-form-sense-row', [
                                'idx' => $idx,
                                'translationRu' => $t->text,
                                'formType' => $t->pivot->form_type ?? '',
                                'transcriptionRu' => $t->pivot->transcription_ru ?? '',
                            ])
                        @endforeach
                    @else
                        @include('admin.flashcards.words.partials.word-form-sense-row', [
                            'idx' => 0,
                            'translationRu' => '',
                            'formType' => '',
                            'transcriptionRu' => '',
                        ])
                    @endif
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Update</button>
            <a href="{{ route('flashcards.words.index') }}" class="px-4 py-2 border rounded hover:bg-gray-50">Cancel</a>
        </div>
    </form>
</div>
<script>
    (function () {
        const container = document.getElementById('entries-container');
        const addBtn = document.getElementById('add-entry-row');
        const importBtn = document.getElementById('gemini-import-btn');
        const statusEl = document.getElementById('gemini-import-status');
        if (!container || !addBtn) return;

        function createEntryRow(index, translation, formType, transcriptionOverride) {
            const row = document.createElement('div');
            row.className = 'entry-row relative border border-gray-200 rounded-lg p-4 space-y-3 bg-gray-50/90';
            row.innerHTML = '' +
                '<div class="flex items-center justify-between gap-2">' +
                '<span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Sense</span>' +
                '<button type="button" class="entry-delete min-h-[2.5rem] min-w-[2.5rem] inline-flex items-center justify-center text-lg leading-none text-red-600 hover:bg-red-50 rounded-lg active:bg-red-100" title="Remove sense">×</button>' +
                '</div>' +
                '<div class="space-y-1">' +
                '<label class="block text-xs font-medium text-gray-600">Translation (RU)</label>' +
                '<input type="text" name="new_entries[' + index + '][translation_ru]" class="w-full border rounded px-3 py-2" placeholder="Translation (RU)" autocomplete="off">' +
                '</div>' +
                '<div class="space-y-1">' +
                '<label class="block text-xs font-medium text-gray-600">Form type</label>' +
                '<input type="text" name="new_entries[' + index + '][form_type]" class="w-full border rounded px-3 py-2" placeholder="e.g. noun (masc.)" autocomplete="off">' +
                '</div>' +
                '<div class="space-y-1">' +
                '<label class="block text-xs font-medium text-gray-600">Transcription if different</label>' +
                '<div class="flex gap-2 items-stretch">' +
                '<input type="text" name="new_entries[' + index + '][transcription_ru]" class="flex-1 min-w-0 border rounded px-3 py-2" placeholder="Leave empty to use default above" autocomplete="off">' +
                '<button type="button" class="entry-transcription-stress shrink-0 px-2 py-1 text-xs bg-gray-200 text-gray-800 rounded hover:bg-gray-300" title="Cycle stress to next vowel (left to right)">Stress</button>' +
                '</div>' +
                '</div>';
            row.querySelector('input[name*="[translation_ru]"]').value = translation || '';
            row.querySelector('input[name*="[form_type]"]').value = formType || '';
            row.querySelector('input[name*="[transcription_ru]"]').value = transcriptionOverride || '';
            return row;
        }
        function reindexEntries() {
            const rows = container.querySelectorAll('.entry-row');
            rows.forEach(function (row, i) {
                row.querySelector('input[name*="[translation_ru]"]').name = 'new_entries[' + i + '][translation_ru]';
                row.querySelector('input[name*="[form_type]"]').name = 'new_entries[' + i + '][form_type]';
                row.querySelector('input[name*="[transcription_ru]"]').name = 'new_entries[' + i + '][transcription_ru]';
            });
            index = rows.length;
        }
        function setupDelete(row) {
            const btn = row.querySelector('.entry-delete');
            if (btn) btn.addEventListener('click', function () {
                row.remove();
                if (container.querySelectorAll('.entry-row').length === 0) {
                    const r = createEntryRow(0, '', '', '');
                    container.appendChild(r);
                    setupDelete(r);
                }
                reindexEntries();
            });
        }
        let index = container.querySelectorAll('.entry-row').length;
        container.querySelectorAll('.entry-row').forEach(setupDelete);

        addBtn.addEventListener('click', function () {
            const row = createEntryRow(index, '', '', '');
            container.appendChild(row);
            setupDelete(row);
            index++;
        });

        if (importBtn && statusEl) {
            importBtn.addEventListener('click', function () {
                const wordInput = document.getElementById('form_text');
                if (!wordInput || !wordInput.value.trim()) {
                    alert('Enter a Hebrew form first.');
                    return;
                }
                const word = wordInput.value.trim();
                const url = '{{ route('flashcards.words.import') }}' + '?source=gemini&word=' + encodeURIComponent(word);
                statusEl.textContent = 'Contacting Gemini...';
                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(function (res) {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(function (data) {
                        if (data.error) throw new Error(data.error);
                        const transcription = document.getElementById('transcription_ru');
                        const shoreshEl = document.getElementById('shoresh_root');
                        const freqRank = document.getElementById('frequency_rank');
                        const freqPerM = document.getElementById('frequency_per_million');
                        if (transcription) transcription.value = data.transcription_ru || '';
                        if (shoreshEl) shoreshEl.value = data.shoresh_root || '';
                        if (freqRank && data.frequency_rank != null) freqRank.value = data.frequency_rank;
                        if (freqPerM && data.frequency_per_million != null) freqPerM.value = data.frequency_per_million;
                        const entries = Array.isArray(data.entries) ? data.entries : [];
                        container.innerHTML = '';
                        index = 0;
                        entries.forEach(function (entry) {
                            const row = createEntryRow(index, entry.translation_ru || '', entry.form_type || '', entry.transcription_ru || '');
                            container.appendChild(row);
                            setupDelete(row);
                            index++;
                        });
                        if (index === 0) {
                            const row = createEntryRow(0, '', '', '');
                            container.appendChild(row);
                            setupDelete(row);
                            index = 1;
                        }
                        statusEl.textContent = 'Gemini data loaded.';
                    })
                    .catch(function (err) {
                        console.error(err);
                        statusEl.textContent = 'Gemini error: ' + err.message;
                    });
            });
        }
    })();

    (function () {
        const ACUTE = '\u0301';
        const VOWELS = /[аеёиоуыэюя]/gi;
        function cycleRussianStress(input) {
            if (!input) return;
            let s = input.value || '';
            const noStress = s.replace(/\u0301/g, '');
            const vowelIndices = [];
            let m;
            const re = new RegExp(VOWELS.source, 'g');
            while ((m = re.exec(noStress)) !== null) vowelIndices.push(m.index);
            if (vowelIndices.length === 0) return;
            let currentIdx = -1;
            const acutePos = s.indexOf(ACUTE);
            if (acutePos > 0) {
                const before = s.slice(0, acutePos).replace(/\u0301/g, '');
                const pos = before.length - 1;
                currentIdx = vowelIndices.indexOf(pos);
            }
            const nextIdx = (currentIdx + 1) % vowelIndices.length;
            const insertAt = vowelIndices[nextIdx] + 1;
            const withStress = noStress.slice(0, insertAt) + ACUTE + noStress.slice(insertAt);
            input.value = withStress;
        }
        const mainInput = document.getElementById('transcription_ru');
        const mainBtn = document.getElementById('transcription_ru_cycle_stress');
        if (mainInput && mainBtn) {
            mainBtn.addEventListener('click', function () {
                cycleRussianStress(mainInput);
            });
        }
        const entContainer = document.getElementById('entries-container');
        if (entContainer) {
            entContainer.addEventListener('click', function (e) {
                const t = e.target.closest('.entry-transcription-stress');
                if (!t || !entContainer.contains(t)) return;
                const row = t.closest('.entry-row');
                if (!row) return;
                const inp = row.querySelector('input[name*="[transcription_ru]"]');
                cycleRussianStress(inp);
            });
        }
    })();
</script>
@endsection
