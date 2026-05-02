{{-- Requires WordFormThemeFactory::variables() extract() earlier in the same view (sets $wordFormInputBorderJs, $btnStressSmall). --}}
<script>
    (function () {
        const container = document.getElementById('entries-container');
        const addBtn = document.getElementById('add-entry-row');
        const importSpecs = [
            { el: document.getElementById('db-import-btn'), source: 'db' },
            { el: document.getElementById('gemini-import-btn'), source: 'gemini' },
        ];
        const inputBorder = @json($wordFormInputBorderJs);
        const btnStressSmall = @json($btnStressSmall);

        function getCsrfToken() {
            const tokenInput = document.querySelector('form input[name="_token"]');
            return tokenInput ? tokenInput.value : '';
        }

        function collectExistingRuTranslations(excludeRow) {
            const out = [];
            if (!container) {
                return out;
            }
            container.querySelectorAll('.entry-row').forEach(function (row) {
                if (excludeRow && row === excludeRow) {
                    return;
                }
                const inp = row.querySelector('input[name*="[translation_ru]"]');
                if (!inp) {
                    return;
                }
                const v = (inp.value || '').trim();
                if (v !== '') {
                    out.push(v);
                }
            });
            return out;
        }

        function fillEntryRowFromImport(targetRow, ent) {
            const trInp = targetRow.querySelector('input[name*="[translation_ru]"]');
            const ftInp = targetRow.querySelector('input[name*="[form_type]"]');
            const prInp = targetRow.querySelector('input[name*="[transcription_ru]"]');
            if (trInp) {
                trInp.value = ent.translation_ru != null ? String(ent.translation_ru) : '';
            }
            if (ftInp) {
                ftInp.value = ent.form_type != null ? String(ent.form_type) : '';
            }
            if (prInp) {
                prInp.value = ent.transcription_ru != null ? String(ent.transcription_ru) : '';
            }
        }

        function runExtraSenseImport(importBtn, source, targetRow) {
            const wordInput = document.getElementById('form_text');
            if (!wordInput || !wordInput.value.trim()) {
                alert('Enter a Hebrew form first.');
                return;
            }
            const csrf = getCsrfToken();
            if (!csrf) {
                console.error('[flashcards.extraSenseImport] CSRF token not found');
                return;
            }
            const word = wordInput.value.trim();
            const url = @json(route('flashcards.words.import-extra-sense'));
            importBtn.disabled = true;
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    form_text: word,
                    source: source,
                    existing_translations: collectExistingRuTranslations(targetRow),
                    _token: csrf,
                }),
            })
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
                        const err = new Error(data.error || ('HTTP ' + res.status));
                        err.httpStatus = res.status;
                        err.payload = data;
                        throw err;
                    }
                    const ent = data.entry;
                    if (!ent || typeof ent !== 'object') {
                        const err = new Error('Invalid response: missing entry');
                        err.payload = data;
                        throw err;
                    }
                    if (!targetRow || !container || !container.contains(targetRow)) {
                        return;
                    }
                    fillEntryRowFromImport(targetRow, ent);
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

        function createEntryRow(index, translation, formType, transcriptionOverride) {
            const row = document.createElement('div');
            row.className = 'entry-row relative border border-gray-200 rounded-xl p-4 space-y-3 bg-gray-50/80 shadow-sm';
            row.innerHTML = '' +
                '<div class="flex items-center justify-between gap-2 flex-wrap">' +
                '<span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Sense</span>' +
                '<div class="flex items-center gap-1 shrink-0">' +
                '<button type="button" class="entry-import-sense-db ' + btnStressSmall + '" title="Add one sense from database">DB</button>' +
                '<button type="button" class="entry-import-sense-gemini ' + btnStressSmall + '" title="Suggest one more sense (Gemini)">G</button>' +
                '<button type="button" class="entry-delete min-h-[2.5rem] min-w-[2.5rem] inline-flex items-center justify-center text-lg leading-none text-red-600 hover:bg-red-50 rounded-lg active:bg-red-100" title="Remove sense">×</button>' +
                '</div>' +
                '</div>' +
                '<div class="space-y-1">' +
                '<label class="block text-xs font-medium text-gray-600">Translation (RU)</label>' +
                '<input type="text" name="new_entries[' + index + '][translation_ru]" class="w-full ' + inputBorder + '" placeholder="Translation (RU)" autocomplete="off">' +
                '</div>' +
                '<div class="space-y-1">' +
                '<label class="block text-xs font-medium text-gray-600">Form type</label>' +
                '<input type="text" name="new_entries[' + index + '][form_type]" class="w-full ' + inputBorder + '" placeholder="e.g. noun (masc.)" autocomplete="off">' +
                '</div>' +
                '<div class="space-y-1">' +
                '<label class="block text-xs font-medium text-gray-600">Transcription if different</label>' +
                '<div class="flex gap-2 items-stretch">' +
                '<input type="text" name="new_entries[' + index + '][transcription_ru]" class="flex-1 min-w-0 ' + inputBorder + '" placeholder="Leave empty to use default above" autocomplete="off">' +
                '<button type="button" class="entry-transcription-stress shrink-0 ' + btnStressSmall + '" title="Cycle stress to next vowel (left to right)">Stress</button>' +
                '</div>' +
                '</div>';
            row.querySelector('input[name*="[translation_ru]"]').value = translation || '';
            row.querySelector('input[name*="[form_type]"]').value = formType || '';
            row.querySelector('input[name*="[transcription_ru]"]').value = transcriptionOverride || '';
            return row;
        }
        function reindexEntries() {
            if (!container) return;
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
        let index = container ? container.querySelectorAll('.entry-row').length : 0;
        container && container.querySelectorAll('.entry-row').forEach(setupDelete);

        if (container && addBtn) {
            addBtn.addEventListener('click', function () {
                const row = createEntryRow(index, '', '', '');
                container.appendChild(row);
                setupDelete(row);
                index++;
            });
        }

        function clearNumberInput(el) {
            if (!el) {
                return;
            }
            el.value = '';
            if ('defaultValue' in el) {
                el.defaultValue = '';
            }
        }

        function clearImportFilledFields() {
            const shoreshEl = document.getElementById('shoresh_root');
            if (shoreshEl) {
                shoreshEl.value = '';
            }
            const transcription = document.getElementById('transcription_ru');
            if (transcription) {
                transcription.value = '';
            }
            clearNumberInput(document.getElementById('frequency_rank'));
            clearNumberInput(document.getElementById('frequency_per_million'));

            const entriesEl = document.getElementById('entries-container');
            if (!entriesEl) {
                return;
            }
            entriesEl.innerHTML = '';
            index = 0;
            const row = createEntryRow(0, '', '', '');
            entriesEl.appendChild(row);
            setupDelete(row);
            index = 1;
        }

        function runWordImport(importBtn, source) {
            const wordInput = document.getElementById('form_text');
            if (!wordInput || !wordInput.value.trim()) {
                alert('Enter a Hebrew form first.');
                return;
            }
            const word = wordInput.value.trim();
            const url = '{{ route('flashcards.words.import') }}'
                + '?source=' + encodeURIComponent(source)
                + '&word=' + encodeURIComponent(word);

            importBtn.disabled = true;

            fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            })
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
                            clearImportFilledFields();
                            console.log('[flashcards.wordImport]', {
                                service: 'db',
                                outcome: 'not_found',
                                word: word,
                                httpStatus: res.status,
                                code: data.code,
                                message: data.error || null,
                            });
                        }
                        const err = new Error(data.error || ('HTTP ' + res.status));
                        err.httpStatus = res.status;
                        err.payload = data;
                        throw err;
                    }

                    const transcription = document.getElementById('transcription_ru');
                    const shoreshEl = document.getElementById('shoresh_root');
                    const freqRank = document.getElementById('frequency_rank');
                    const freqPerM = document.getElementById('frequency_per_million');

                    if (transcription) {
                        transcription.value = (data.transcription_ru != null && String(data.transcription_ru).trim() !== '')
                            ? data.transcription_ru
                            : '';
                    }
                    if (shoreshEl) {
                        shoreshEl.value = (data.shoresh_root != null && String(data.shoresh_root).trim() !== '')
                            ? data.shoresh_root
                            : '';
                    }
                    if (freqRank) {
                        if (data.frequency_rank !== null && typeof data.frequency_rank !== 'undefined') {
                            freqRank.value = data.frequency_rank;
                        } else {
                            clearNumberInput(freqRank);
                        }
                    }
                    if (freqPerM) {
                        if (data.frequency_per_million !== null && typeof data.frequency_per_million !== 'undefined') {
                            freqPerM.value = data.frequency_per_million;
                        } else {
                            clearNumberInput(freqPerM);
                        }
                    }

                    const entries = Array.isArray(data.entries) ? data.entries : [];

                    const entriesContainer = document.getElementById('entries-container');
                    if (!entriesContainer) {
                        console.error('[flashcards.wordImport] #entries-container not found');
                        return;
                    }
                    entriesContainer.innerHTML = '';
                    index = 0;
                    entries.forEach(function (entry) {
                        const row = createEntryRow(
                            index,
                            entry.translation_ru || '',
                            entry.form_type || '',
                            entry.transcription_ru || ''
                        );
                        entriesContainer.appendChild(row);
                        setupDelete(row);
                        index++;
                    });

                    if (index === 0) {
                        const row = createEntryRow(0, '', '', '');
                        entriesContainer.appendChild(row);
                        setupDelete(row);
                        index = 1;
                    }
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

        if (container) {
            container.addEventListener('click', function (e) {
                const db = e.target.closest('.entry-import-sense-db');
                const gem = e.target.closest('.entry-import-sense-gemini');
                if (!db && !gem) {
                    return;
                }
                const btn = db || gem;
                const row = btn.closest('.entry-row');
                if (!row || !container.contains(row)) {
                    return;
                }
                e.preventDefault();
                const source = db ? 'db' : 'gemini';
                runExtraSenseImport(btn, source, row);
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

        const container = document.getElementById('entries-container');
        if (container) {
            container.addEventListener('click', function (e) {
                const t = e.target.closest('.entry-transcription-stress');
                if (!t || !container.contains(t)) return;
                const row = t.closest('.entry-row');
                if (!row) return;
                const inp = row.querySelector('input[name*="[transcription_ru]"]');
                cycleRussianStress(inp);
            });
        }
    })();
</script>
