{{-- Requires WordFormThemeFactory::variables() extract() in parent view. --}}
<script>
    (function () {
        const container = document.getElementById('entries-container');
        const addBtn = document.getElementById('add-hebrew-form-row');
        const importSpecs = [
            { el: document.getElementById('db-import-btn'), source: 'db' },
            { el: document.getElementById('gemini-import-btn'), source: 'gemini' },
        ];
        const inputClass = @json($inputClass);
        const inputClassLg = @json($inputClassLg);
        const btnStressSmall = @json($btnStressSmall);
        const formTypeListId = 'flashcards-form-type-options';

        function formTypeForGloss(candidate, gloss) {
            if (candidate.form_type != null && String(candidate.form_type).trim() !== '') {
                return String(candidate.form_type);
            }
            const entries = Array.isArray(candidate.entries) ? candidate.entries : [];
            const g = (gloss || '').trim().toLowerCase();
            let i;
            for (i = 0; i < entries.length; i++) {
                const e = entries[i];
                const t = (e.translation_ru != null ? String(e.translation_ru) : '').trim().toLowerCase();
                if (t === g && e.form_type != null && String(e.form_type).trim() !== '') {
                    return String(e.form_type);
                }
            }
            for (i = 0; i < entries.length; i++) {
                if (entries[i].form_type != null && String(entries[i].form_type).trim() !== '') {
                    return String(entries[i].form_type);
                }
            }
            return '';
        }

        function createHebrewFormRow(index, data) {
            data = data || {};
            const row = document.createElement('div');
            row.className = 'hebrew-form-row entry-row relative border border-gray-200 rounded-xl p-4 space-y-3 bg-gray-50/80 shadow-sm';
            const ft = data.form_text != null ? String(data.form_text) : '';
            const sr = data.shoresh_root != null ? String(data.shoresh_root) : '';
            const fty = data.form_type != null ? String(data.form_type) : '';
            const tr = data.transcription_ru != null ? String(data.transcription_ru) : '';
            const fr = data.frequency_rank != null && data.frequency_rank !== '' ? String(data.frequency_rank) : '';
            const fm = data.frequency_per_million != null && data.frequency_per_million !== '' ? String(data.frequency_per_million) : '';
            row.innerHTML = ''
                + '<div class="flex items-center justify-between gap-2 flex-wrap">'
                + '<span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Hebrew form</span>'
                + '<button type="button" class="hebrew-form-row-delete min-h-[2.5rem] min-w-[2.5rem] inline-flex items-center justify-center text-lg leading-none text-red-600 hover:bg-red-50 rounded-lg active:bg-red-100" title="Remove Hebrew form">×</button>'
                + '</div>'
                + '<div class="space-y-1"><label class="block text-xs font-medium text-gray-600">Hebrew form <span class="text-red-600">*</span></label>'
                + '<input type="text" name="hebrew_forms[' + index + '][form_text]" required dir="rtl" class="w-full ' + inputClassLg + '" autocomplete="off"></div>'
                + '<div class="space-y-1"><label class="block text-xs font-medium text-gray-600">Root</label>'
                + '<input type="text" name="hebrew_forms[' + index + '][shoresh_root]" dir="rtl" placeholder="e.g. שלמ" class="w-full ' + inputClass + '" autocomplete="off"></div>'
                + '<div class="space-y-1"><label class="block text-xs font-medium text-gray-600">Form type</label>'
                + '<input type="text" name="hebrew_forms[' + index + '][form_type]" class="w-full ' + inputClass + '" list="' + formTypeListId + '" placeholder="Choose a suggestion or type (aliases OK)" autocomplete="off"></div>'
                + '<div class="space-y-1"><label class="block text-xs font-medium text-gray-600">Transcription</label>'
                + '<div class="flex gap-2 items-stretch">'
                + '<input type="text" name="hebrew_forms[' + index + '][transcription_ru]" class="flex-1 min-w-0 ' + inputClass + '" autocomplete="off">'
                + '<button type="button" class="hebrew-form-row-transcription-stress shrink-0 ' + btnStressSmall + '" title="Cycle stress">Stress</button>'
                + '</div></div>'
                + '<div class="grid grid-cols-2 gap-3">'
                + '<div class="space-y-1"><label class="block text-xs font-medium text-gray-600">Frequency rank</label>'
                + '<input type="number" name="hebrew_forms[' + index + '][frequency_rank]" min="1" class="w-full ' + inputClass + '" autocomplete="off"></div>'
                + '<div class="space-y-1"><label class="block text-xs font-medium text-gray-600">Per million</label>'
                + '<input type="number" name="hebrew_forms[' + index + '][frequency_per_million]" step="0.01" min="0" class="w-full ' + inputClass + '" autocomplete="off"></div>'
                + '</div>'
                + '<div><input type="hidden" name="hebrew_forms[' + index + '][add_to_deck]" value="0">'
                + '<label class="inline-flex items-center text-sm text-gray-700">'
                + '<input type="checkbox" name="hebrew_forms[' + index + '][add_to_deck]" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">'
                + '<span class="ml-2">Add to my deck</span></label></div>';
            row.querySelector('input[name*="[form_text]"]').value = ft;
            row.querySelector('input[name*="[shoresh_root]"]').value = sr;
            row.querySelector('input[name*="[form_type]"]').value = fty;
            row.querySelector('input[name*="[transcription_ru]"]').value = tr;
            if (fr !== '') {
                row.querySelector('input[name*="[frequency_rank]"]').value = fr;
            }
            if (fm !== '') {
                row.querySelector('input[name*="[frequency_per_million]"]').value = fm;
            }
            return row;
        }

        function reindexHebrewRows() {
            if (!container) {
                return;
            }
            const rows = container.querySelectorAll('.hebrew-form-row');
            rows.forEach(function (row, i) {
                row.querySelector('input[name*="[form_text]"]').name = 'hebrew_forms[' + i + '][form_text]';
                row.querySelector('input[name*="[shoresh_root]"]').name = 'hebrew_forms[' + i + '][shoresh_root]';
                row.querySelector('input[name*="[form_type]"]').name = 'hebrew_forms[' + i + '][form_type]';
                row.querySelector('input[name*="[transcription_ru]"]').name = 'hebrew_forms[' + i + '][transcription_ru]';
                row.querySelector('input[name*="[frequency_rank]"]').name = 'hebrew_forms[' + i + '][frequency_rank]';
                row.querySelector('input[name*="[frequency_per_million]"]').name = 'hebrew_forms[' + i + '][frequency_per_million]';
                const hiddenDeck = row.querySelector('input[type="hidden"][name*="[add_to_deck]"]');
                const cbDeck = row.querySelector('input[type="checkbox"][name*="[add_to_deck]"]');
                if (hiddenDeck) {
                    hiddenDeck.name = 'hebrew_forms[' + i + '][add_to_deck]';
                }
                if (cbDeck) {
                    cbDeck.name = 'hebrew_forms[' + i + '][add_to_deck]';
                }
            });
            index = rows.length;
        }

        function setupDelete(row) {
            const btn = row.querySelector('.hebrew-form-row-delete');
            if (!btn) {
                return;
            }
            btn.addEventListener('click', function () {
                row.remove();
                if (container.querySelectorAll('.hebrew-form-row').length === 0) {
                    const r = createHebrewFormRow(0, {});
                    container.appendChild(r);
                    setupDelete(r);
                }
                reindexHebrewRows();
            });
        }

        let index = container ? container.querySelectorAll('.hebrew-form-row').length : 0;
        if (container) {
            container.querySelectorAll('.hebrew-form-row').forEach(setupDelete);
        }

        if (container && addBtn) {
            addBtn.addEventListener('click', function () {
                const row = createHebrewFormRow(index, {});
                container.appendChild(row);
                setupDelete(row);
                index++;
            });
        }

        function fillContainerFromCandidates(candidates) {
            if (!container) {
                return;
            }
            container.innerHTML = '';
            index = 0;
            const glossInput = document.getElementById('russian_gloss');
            const gloss = glossInput ? glossInput.value.trim() : '';
            candidates.forEach(function (c) {
                const enriched = Object.assign({}, c);
                if (!enriched.form_type || String(enriched.form_type).trim() === '') {
                    enriched.form_type = formTypeForGloss(c, gloss);
                }
                const row = createHebrewFormRow(index, enriched);
                container.appendChild(row);
                setupDelete(row);
                index++;
            });
            if (index === 0) {
                const row = createHebrewFormRow(0, {});
                container.appendChild(row);
                setupDelete(row);
                index = 1;
            }
        }

        function runWordImport(importBtn, source) {
            const glossInput = document.getElementById('russian_gloss');
            if (!glossInput || !glossInput.value.trim()) {
                alert('Enter the Russian word first.');
                return;
            }
            const word = glossInput.value.trim();
            const url = @json(route('flashcards.words.import'))
                + '?source=' + encodeURIComponent(source)
                + '&word=' + encodeURIComponent(word)
                + '&from_russian=1';

            importBtn.disabled = true;
            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(function (res) {
                    return res.json().then(function (body) {
                        return { res: res, body: body };
                    }).catch(function () {
                        return { res: res, body: {} };
                    });
                })
                .then(function (out) {
                    const res = out.res;
                    const data = out.body && typeof out.body === 'object' ? out.body : {};
                    if (!res.ok || data.error) {
                        if (source === 'db' && data.code === 'WORD_NOT_IN_DATABASE') {
                            fillContainerFromCandidates([]);
                        }
                        const err = new Error(data.error || ('HTTP ' + res.status));
                        err.payload = data;
                        throw err;
                    }
                    if (data.candidates && Array.isArray(data.candidates) && data.candidates.length > 0) {
                        fillContainerFromCandidates(data.candidates);
                        return;
                    }
                    fillContainerFromCandidates([]);
                })
                .catch(function (err) {
                    if (err && err.payload) {
                        console.error(err.message || err, err.payload);
                    } else {
                        console.error(err);
                    }
                })
                .finally(function () {
                    importBtn.disabled = false;
                });
        }

        importSpecs.forEach(function (spec) {
            if (!spec.el) {
                return;
            }
            spec.el.addEventListener('click', function () {
                runWordImport(spec.el, spec.source);
            });
        });
    })();

    (function () {
        const ACUTE = '\u0301';
        const VOWELS = /[аеёиоуыэюя]/gi;

        function cycleRussianStress(input) {
            if (!input) {
                return;
            }
            let s = input.value || '';
            const noStress = s.replace(/\u0301/g, '');
            const vowelIndices = [];
            let m;
            const re = new RegExp(VOWELS.source, 'g');
            while ((m = re.exec(noStress)) !== null) {
                vowelIndices.push(m.index);
            }
            if (vowelIndices.length === 0) {
                return;
            }
            let currentIdx = -1;
            const acutePos = s.indexOf(ACUTE);
            if (acutePos > 0) {
                const before = s.slice(0, acutePos).replace(/\u0301/g, '');
                const pos = before.length - 1;
                currentIdx = vowelIndices.indexOf(pos);
            }
            const nextIdx = (currentIdx + 1) % vowelIndices.length;
            const insertAt = vowelIndices[nextIdx] + 1;
            input.value = noStress.slice(0, insertAt) + ACUTE + noStress.slice(insertAt);
        }

        const container = document.getElementById('entries-container');
        if (container) {
            container.addEventListener('click', function (e) {
                const t = e.target.closest('.hebrew-form-row-transcription-stress');
                if (!t || !container.contains(t)) {
                    return;
                }
                const row = t.closest('.hebrew-form-row');
                if (!row) {
                    return;
                }
                const inp = row.querySelector('input[name*="[transcription_ru]"]');
                cycleRussianStress(inp);
            });
        }
    })();
</script>
