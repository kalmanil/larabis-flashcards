@extends('layouts.app')

@section('title', 'Process new words - Flashcards')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-3xl font-bold mb-2">Process new words</h1>
    <p class="text-gray-600 mb-6">
        Word <span class="font-semibold">{{ $pendingPosition }}</span> of <span class="font-semibold">{{ $pendingTotal }}</span> pending enrichment
        <span class="mx-2 text-gray-400">·</span>
        <span dir="rtl" class="font-medium">{{ $word->form_text }}</span>
    </p>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <form action="{{ route('flashcards.words.update', $word) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block font-medium text-gray-700 mb-1">Hebrew form *</label>
            <div class="flex gap-2">
                <input type="text" name="form_text" id="form_text" value="{{ old('form_text', $word->form_text) }}" required dir="rtl" readonly
                       class="flex-1 border rounded px-3 py-2 text-lg bg-gray-50">
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
            <p class="text-sm text-gray-500 mb-1">Each sense: translation + form type. Links existing or creates if missing.</p>
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
                                       class="w-full border rounded px-3 py-2"
                                       placeholder="Translation (RU)">
                                <input type="text"
                                       name="new_entries[{{ $idx }}][form_type]"
                                       value="{{ $entry['form_type'] ?? '' }}"
                                       class="w-full border rounded px-3 py-2"
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
                                       class="w-full border rounded px-3 py-2"
                                       placeholder="Translation (RU)">
                                <input type="text"
                                       name="new_entries[{{ $idx }}][form_type]"
                                       value="{{ $t->pivot->form_type ?? '' }}"
                                       class="w-full border rounded px-3 py-2"
                                       placeholder="Form type (e.g. noun (masc.))">
                                <button type="button" class="entry-delete px-2 py-1 text-red-600 hover:bg-red-50 rounded" title="Remove sense">×</button>
                            </div>
                        @endforeach
                    @else
                        <div class="grid grid-cols-[1fr_1fr_auto] gap-2 entry-row items-center">
                            <input type="text"
                                   name="new_entries[0][translation_ru]"
                                   class="w-full border rounded px-3 py-2"
                                   placeholder="Translation (RU)">
                            <input type="text"
                                   name="new_entries[0][form_type]"
                                   class="w-full border rounded px-3 py-2"
                                   placeholder="Form type (e.g. noun (masc.))">
                            <button type="button" class="entry-delete px-2 py-1 text-red-600 hover:bg-red-50 rounded" title="Remove sense">×</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" name="enrichment_flow" value="1" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save and continue</button>
            <button type="submit" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Save and exit</button>
            <a href="{{ route('flashcards.dashboard') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Exit without saving</a>
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

        function createEntryRow(index, translation, formType) {
            const row = document.createElement('div');
            row.className = 'grid grid-cols-[1fr_1fr_auto] gap-2 entry-row items-center';
            row.innerHTML = '' +
                '<input type="text" name="new_entries[' + index + '][translation_ru]" class="w-full border rounded px-3 py-2" placeholder="Translation (RU)">' +
                '<input type="text" name="new_entries[' + index + '][form_type]" class="w-full border rounded px-3 py-2" placeholder="Form type (e.g. noun (masc.))">' +
                '<button type="button" class="entry-delete px-2 py-1 text-red-600 hover:bg-red-50 rounded" title="Remove sense">×</button>';
            row.querySelector('input[name*="[translation_ru]"]').value = translation || '';
            row.querySelector('input[name*="[form_type]"]').value = formType || '';
            return row;
        }
        function reindexEntries() {
            const rows = container.querySelectorAll('.entry-row');
            rows.forEach(function (row, i) {
                row.querySelector('input[name*="[translation_ru]"]').name = 'new_entries[' + i + '][translation_ru]';
                row.querySelector('input[name*="[form_type]"]').name = 'new_entries[' + i + '][form_type]';
            });
            index = rows.length;
        }
        function setupDelete(row) {
            const btn = row.querySelector('.entry-delete');
            if (btn) btn.addEventListener('click', function () {
                row.remove();
                if (container.querySelectorAll('.entry-row').length === 0) {
                    const r = createEntryRow(0, '', '');
                    container.appendChild(r);
                    setupDelete(r);
                }
                reindexEntries();
            });
        }
        let index = container.querySelectorAll('.entry-row').length;
        container.querySelectorAll('.entry-row').forEach(setupDelete);

        addBtn.addEventListener('click', function () {
            const row = createEntryRow(index, '', '');
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
                            const row = createEntryRow(index, entry.translation_ru || '', entry.form_type || '');
                            container.appendChild(row);
                            setupDelete(row);
                            index++;
                        });
                        if (index === 0) {
                            const row = createEntryRow(0, '', '');
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
        const input = document.getElementById('transcription_ru');
        const btn = document.getElementById('transcription_ru_cycle_stress');
        if (!input || !btn) return;
        btn.addEventListener('click', function () {
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
        });
    })();
</script>
@endsection
