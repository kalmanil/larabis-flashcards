{{-- Requires WordFormTheme::variables() extract() earlier in the same view (sets $wordFormInputBorderJs). --}}
<script>
    (function () {
        const container = document.getElementById('entries-container');
        const addBtn = document.getElementById('add-entry-row');
        const importBtn = document.getElementById('gemini-import-btn');
        const statusEl = document.getElementById('gemini-import-status');
        const inputBorder = @json($wordFormInputBorderJs);

        function createEntryRow(index, translation, formType) {
            const row = document.createElement('div');
            row.className = 'grid grid-cols-[1fr_1fr_auto] gap-2 entry-row items-center';
            row.innerHTML = '' +
                '<input type="text" name="new_entries[' + index + '][translation_ru]" class="w-full ' + inputBorder + '" placeholder="Translation (RU)">' +
                '<input type="text" name="new_entries[' + index + '][form_type]" class="w-full ' + inputBorder + '" placeholder="Form type (e.g. noun (masc.))">' +
                '<button type="button" class="entry-delete px-2 py-1 text-red-600 hover:bg-red-50 rounded" title="Remove sense">×</button>';
            row.querySelector('input[name*="[translation_ru]"]').value = translation || '';
            row.querySelector('input[name*="[form_type]"]').value = formType || '';
            return row;
        }
        function reindexEntries() {
            if (!container) return;
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
        let index = container ? container.querySelectorAll('.entry-row').length : 0;
        container && container.querySelectorAll('.entry-row').forEach(setupDelete);

        if (container && addBtn) {
            addBtn.addEventListener('click', function () {
                const row = createEntryRow(index, '', '');
                container.appendChild(row);
                setupDelete(row);
                index++;
            });
        }

        if (importBtn && container) {
            importBtn.addEventListener('click', function () {
                const wordInput = document.getElementById('form_text');
                if (!wordInput || !wordInput.value.trim()) {
                    alert('Enter a Hebrew form first.');
                    return;
                }
                const word = wordInput.value.trim();
                const url = '{{ route('flashcards.words.import') }}' + '?source=gemini&word=' + encodeURIComponent(word);

                if (statusEl) statusEl.textContent = 'Contacting Gemini...';

                fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                    .then(function (res) {
                        if (!res.ok) {
                            throw new Error('HTTP ' + res.status);
                        }
                        return res.json();
                    })
                    .then(function (data) {
                        if (data.error) {
                            throw new Error(data.error);
                        }

                        const transcription = document.getElementById('transcription_ru');
                        const freqRank = document.getElementById('frequency_rank');
                        const freqPerM = document.getElementById('frequency_per_million');

                        if (transcription && data.transcription_ru) {
                            transcription.value = data.transcription_ru;
                        }
                        const shoreshEl = document.getElementById('shoresh_root');
                        if (shoreshEl && data.shoresh_root) {
                            shoreshEl.value = data.shoresh_root;
                        }
                        if (freqRank && data.frequency_rank !== null && typeof data.frequency_rank !== 'undefined') {
                            freqRank.value = data.frequency_rank;
                        }
                        if (freqPerM && data.frequency_per_million !== null && typeof data.frequency_per_million !== 'undefined') {
                            freqPerM.value = data.frequency_per_million;
                        }

                        const entries = Array.isArray(data.entries) ? data.entries : [];

                        container.innerHTML = '';
                        index = 0;
                        entries.forEach(function (entry) {
                            const row = createEntryRow(
                                index,
                                entry.translation_ru || '',
                                entry.form_type || ''
                            );
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

                        if (statusEl) statusEl.textContent = 'Gemini data loaded.';
                    })
                    .catch(function (err) {
                        console.error(err);
                        if (statusEl) statusEl.textContent = 'Gemini error: ' + err.message;
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
