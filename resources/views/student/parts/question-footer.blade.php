<footer id="question-footer" class="w-full bg-white border-t shadow-sm fixed bottom-0 left-0 right-0 z-50">
    <div class="mx-auto px-4 py-3 flex justify-between">
        <!-- Left buttons -->
        <div class="flex items-center space-x-2">
            <button type="button" title="Menu" class="btn-square btn-base-large rounded">
                <svg class="h-5 w-5 text-gray-600" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>

            <button type="button" title="Info" class="btn-square btn-base rounded">
                <svg class="h-5 w-5 text-gray-600" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path d="M12 8v4M12 16h.01M12 4a8 8 0 110 16 8 8 0 010-16z" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>

            <div class="ml-3 text-sm text-gray-600">
                <span class="hidden sm:inline">Ghi chú / Dịch</span>
            </div>
        </div>

        <!-- Right buttons -->
        <div class="ml-auto flex items-center space-x-3">
            <button type="button" title="Thoát" class="btn-square btn-base rounded text-gray-700"
                onclick="window.location.href='{{ route('student.dashboard') }}'">
                <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" />
                </svg>
                <span class="sr-only">Thoát</span>
            </button>

            <button type="button" class="btn-base-large btn-primary rounded flex items-center" id="footer-next-btn">
                <span id="footer-next-label" class="mr-2">Kiểm tra</span>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path d="M5 12h14M13 5l7 7-7 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
                <svg id="footer-next-spinner" class="hidden ml-2 h-5 w-5 animate-spin" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                        fill="none"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
            </button>
        </div>
    </div>
</footer>

<script>
    (function () {
        if (window.__aptis_footer_initted) return; window.__aptis_footer_initted = true;

        const fnext = document.getElementById('footer-next-btn');
        const spinner = document.getElementById('footer-next-spinner');
        const mainSelector = '.container.mx-auto';
    if (!fnext) { try { /* debug removed: next button not found */ } catch (e) {} }

        window.attemptAnswers = window.attemptAnswers || {};
        window.__aptis_feedbackShownForQid = window.__aptis_feedbackShownForQid || {};
        // Track last-focused question block so the footer uses the question the user
        // was interacting with (clicking footer steals focus from inputs).
        window.__aptis_lastFocusedQid = window.__aptis_lastFocusedQid || null;
        document.addEventListener('focusin', (ev) => {
            try {
                const b = ev.target && ev.target.closest ? ev.target.closest('.question-block') : null;
                if (b && b.dataset && b.dataset.qid) {
                    window.__aptis_lastFocusedQid = b.dataset.qid;
                }
            } catch (e) { /* ignore */ }
        }, true);
        // Debounced persistence to reduce main-thread pauses caused by JSON.stringify
        let __aptis_persist_timer = null;
        function persistAnswersNow() {
            try {
                if (!window.currentAttemptId) return;
                localStorage.setItem('attempt_answers_' + window.currentAttemptId, JSON.stringify(window.attemptAnswers));
            } catch (e) { }
        }
        function schedulePersistAnswers(delay = 300) {
            if (__aptis_persist_timer) clearTimeout(__aptis_persist_timer);
            __aptis_persist_timer = setTimeout(() => { __aptis_persist_timer = null; persistAnswersNow(); }, delay);
        }
        window.addEventListener('beforeunload', () => { if (__aptis_persist_timer) clearTimeout(__aptis_persist_timer); persistAnswersNow(); });

        const footer = {
            setLoading(on) { spinner?.classList.toggle('hidden', !on); fnext.disabled = on; },
            getActiveBlock() {
                const blocks = [...document.querySelectorAll('.question-block')];
                if (!blocks.length) return null;
                if (blocks.length === 1) return blocks[0];


                let bestBlock = blocks[0];
                let bestVisibility = 0;

                blocks.forEach(block => {
                    const rect = block.getBoundingClientRect();

                    const visibleHeight = Math.min(rect.bottom, window.innerHeight) - Math.max(rect.top, 0);
                    const visibleWidth = Math.min(rect.right, window.innerWidth) - Math.max(rect.left, 0);
                    const visibleArea = visibleHeight * visibleWidth;

                    if (visibleArea > bestVisibility) {
                        bestVisibility = visibleArea;
                        bestBlock = block;
                    }
                });

                return bestBlock;
            },
            getQid() {
                const activeBlock = this.getActiveBlock();
                return activeBlock?.dataset.qid || null;
            },
            collectAnswers(root) {
                if (!root) {
                    root = this.getActiveBlock();
                    if (!root) return null;
                }

                if (root.querySelector('.slot')) {
                    const order = [], texts = [];
                    root.querySelectorAll('.slot').forEach(s => {
                        const it = s.querySelector('.draggable-item');
                        if (it) { order.push(it.dataset.index); texts.push(it.innerText.trim()); }
                        else order.push(null);
                    });
                    return { part: 'part2', order, texts };
                }

                const part2Els = root.querySelectorAll('.part2-select');
                if (part2Els.length) {
                    const order = [], texts = [], originalIndices = [];
                    part2Els.forEach(s => {
                        const v = s.value === '' ? null : s.value;
                        order.push(v === null ? null : Number(v));
                        texts.push(v === null ? null : s.options[s.selectedIndex].text);


                        if (v !== null && s.options[s.selectedIndex].hasAttribute('data-original-index')) {
                            originalIndices.push(Number(s.options[s.selectedIndex].getAttribute('data-original-index')));
                        } else {
                            originalIndices.push(v === null ? null : Number(v));
                        }
                    });
                    return { part: 'part2', order, texts, originalIndices };
                }

                const checked = root.querySelectorAll('input:checked');
                if (checked.length) {
                    return { part: 'choice', value: Array.from(checked).map(i => i.value) };
                }


                const part3Els = root.querySelectorAll('select[name^="select-"]');
                if (part3Els.length) {
                    return { part: 'part3', value: Array.from(part3Els).map(s => s.value) };
                }


                const selEls = root.querySelectorAll('select');
                if (selEls.length > 1) {
                    // Try to infer the question part from per-block metadata (preferred)
                    try {
                        let inferredPart = null;
                        // 1) data-meta attribute on block (stringified JSON)
                        if (root && root.dataset && root.dataset.metadata) {
                            const dm = JSON.parse(root.dataset.metadata);
                            if (dm && (dm.part === 4 || (dm.paragraphs && dm.paragraphs.length))) inferredPart = 4;
                        }
                        // 2) hidden data-meta-json element (result page)
                        if (!inferredPart) {
                            const metaEl = root.querySelector('[data-meta-json]');
                            if (metaEl) {
                                const dm = JSON.parse(metaEl.getAttribute('data-meta-json'));
                                if (dm && (dm.part === 4 || (dm.paragraphs && dm.paragraphs.length))) inferredPart = 4;
                            }
                        }
                        // 3) fallback to global currentQuestionMeta
                        if (!inferredPart && window.currentQuestionMeta && (window.currentQuestionMeta.part === 4 || (window.currentQuestionMeta.paragraphs && window.currentQuestionMeta.paragraphs.length))) {
                            inferredPart = 4;
                        }

                        // If metadata strongly indicates part4, we still apply a defensive heuristic:
                        // when all selects are embedded inline inside a `.prose` element (typical for
                        // part1 blanks), prefer treating this as part1 to avoid mis-detection caused by
                        // stray metadata (observed when `meta.paragraphs` exists but the UI is part1).
                        try {
                            if (inferredPart === 4) {
                                const isInlineSelect = (s) => {
                                    try {
                                        if (s.closest && s.closest('.prose')) return true;
                                        if (s.closest && s.closest('p')) return true;
                                        if (s.closest && s.closest('span')) return true;
                                        if (s.classList && s.classList.contains('inline-block')) return true;
                                    } catch (e) {}
                                    return false;
                                };
                                const inlineCount = Array.from(selEls).filter(s => isInlineSelect(s)).length;
                                // prefer part1 when a clear majority of selects are inline (>=60%)
                                if (inlineCount >= Math.ceil(selEls.length * 0.6)) {
                                    inferredPart = null;
                                }
                            }
                        } catch (e) { /* ignore and respect inferredPart if error */ }

                        if (inferredPart === 4) return { part: 'part4', value: Array.from(selEls).map(s => s.value) };
                    } catch (e) { /* ignore parsing errors and fallthrough */ }

                    // default to part1 when no metadata indicates part4
                    return { part: 'part1', value: Array.from(selEls).map(s => s.value) };
                }

                if (selEls.length === 1) {
                    return { part: 'select', value: selEls[0].value };
                }

                const ta = root.querySelector('textarea');
                if (ta) {
                    return { part: 'text', value: ta.value };
                }

                return null;
            },
            saveAnswer(qid, payload) {
                if (!qid) return;
                window.attemptAnswers = window.attemptAnswers || {};
                window.attemptAnswers[qid] = payload;
                if (typeof schedulePersistAnswers === 'function') schedulePersistAnswers();
            },
            displayFeedback(qid, payload) {
                if (!payload) return;

                function renderFeedback(qid, stats, rows, rowRenderer, space = 'space-y-2') {
                    const target = document.querySelector(`.inline-feedback[data-qid-feedback="${qid}"]`);
                    if (!target) return;

                    if (target.hasAttribute('data-feedback-rendered')) return;
                    target.setAttribute('data-feedback-rendered', 'true');

                    const container = document.createElement('div');
                    container.className = 'w-full';
                    container.innerHTML = `
                        <div class="flex items-center justify-between mb-2">
                            <div class="font-medium text-sm">Đã lưu</div>
                            <div class="text-xs font-semibold px-2 py-0.5 rounded-full text-gray-700 bg-gray-100">${stats}</div>
                        </div>
                        `;
                    const list = document.createElement('div');
                    list.className = space;
                    rows.forEach(r => list.appendChild(rowRenderer(r)));
                    container.appendChild(list);
                    target.innerHTML = '';
                    target.appendChild(container);
                    target.classList.remove('hidden');
                }

                const rowBuilders = {
                    twoCols: (r) => {
                        const row = document.createElement('div');
                        row.className = `grid grid-cols-2 gap-4 p-2 border rounded ${r.ok ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'}`;
                        const userCol = document.createElement('div');
                        userCol.className = 'text-sm text-gray-800';

                        // context (sentence containing the blank)
                        if (r.context) {
                            const ctx = document.createElement('div');
                            ctx.className = 'text-xs italic text-gray-500 mb-1';
                            ctx.textContent = r.context;
                            userCol.appendChild(ctx);
                        }

                        const userVal = document.createElement('div');
                        userVal.className = 'flex items-center gap-2';
                        if (r.ok) {
                            const svg = document.createElement('svg');
                            svg.setAttribute('class','h-4 w-4 text-green-600 flex-shrink-0');
                            svg.setAttribute('viewBox','0 0 20 20');
                            svg.setAttribute('fill','currentColor');
                            svg.setAttribute('aria-hidden','true');
                            svg.innerHTML = '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414-1.414L8 11.172 4.707 7.879a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8z" clip-rule="evenodd"/>';
                            userVal.appendChild(svg);
                            const span = document.createElement('span');
                            span.textContent = r.userText;
                            userVal.appendChild(span);
                        } else {
                            const span = document.createElement('div');
                            span.textContent = r.userText;
                            userVal.appendChild(span);
                        }

                        userCol.appendChild(userVal);

                        const corrCol = document.createElement('div');
                        corrCol.className = 'text-sm text-gray-800';
                        corrCol.textContent = r.correctText;
                        row.appendChild(userCol);
                        row.appendChild(corrCol);
                        return row;
                    },
                    paragraph: (r) => {
                        const row = document.createElement('div');
                        row.className = `p-2 border rounded ${r.ok ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'}`;
                        let inner = `<div class="text-sm italic text-gray-600 mb-2">${r.para}</div>`;
                        if (r.ok) {
                            inner += `<div class="flex items-center gap-2 text-sm text-gray-800">` +
                                `<svg class="h-4 w-4 text-green-600 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414-1.414L8 11.172 4.707 7.879a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8z" clip-rule="evenodd"/></svg>` +
                                `<div>Bạn chọn: ${r.userText}</div></div>`;
                        } else {
                            inner += `<div class="text-sm text-gray-800">Bạn chọn: ${r.userText}</div>`;
                            inner += `<div class="text-sm text-gray-800">Đáp án đúng: ${r.corrText}</div>`;
                        }
                        row.innerHTML = inner;
                        return row;
                    }
                };


                if (!window.inlineFeedback) {
                    window.inlineFeedback = {
                        show: function (qid, userAnswer, correctAnswer, statsText) {
                            const target = document.querySelector(`.inline-feedback[data-qid-feedback="${qid}"]`);
                            if (!target) return;

                            if (target.hasAttribute('data-feedback-rendered')) return;
                            target.setAttribute('data-feedback-rendered', 'true');

                            const container = document.createElement('div');
                            container.className = 'w-full';
                            container.innerHTML = `
                                <div class="flex items-center justify-between mb-2">
                                    <div class="font-medium text-sm">Đã lưu</div>
                                    <div class="text-xs font-semibold px-2 py-0.5 rounded-full text-gray-700 bg-gray-100">${statsText}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mt-2">
                                    <div>
                                        <div class="text-xs text-gray-500">Bạn chọn</div>
                                        <div class="text-sm text-gray-700 mt-1">${userAnswer}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Đáp án</div>
                                        <div class="text-sm text-gray-700 mt-1">${correctAnswer}</div>
                                    </div>
                                </div>
                            `;
                            target.innerHTML = '';
                            target.appendChild(container);
                            target.classList.remove('hidden');
                        },
                        hide: function (qid) {
                            const target = document.querySelector(`.inline-feedback[data-qid-feedback="${qid}"]`);
                            if (target) {
                                target.innerHTML = '';
                                target.classList.add('hidden');
                                target.removeAttribute('data-feedback-rendered');
                            }
                        }
                    };
                }

                function renderPart1(qid, payload) {
                    const questionBlock = document.querySelector(`.question-block[data-qid="${qid}"]`);
                    const selEls = questionBlock ? [...questionBlock.querySelectorAll('select')] : [];
                    try {
                        if (questionBlock) {
                            const prose = questionBlock.querySelector('.prose');
                        }
                    } catch (e) { }

                    const userValsFromDom = selEls.length ? selEls.map(s => s.value || null) : null;
                    const userVals = userValsFromDom ?? (payload?.value ? Array.from(payload.value) : []);
                    let meta = null;
                    try {
                        if (questionBlock && questionBlock.dataset.metadata) {
                            meta = JSON.parse(questionBlock.dataset.metadata);
                        }
                    } catch (e) { }


                    meta = meta || window.currentQuestionMeta || {};

                    let expected = meta.correct_answers || meta.correctAnswers || meta.answers || meta.key || meta.correct || [];
                    expected = expected.map(e => typeof e === 'object' ? (e.text ?? e.label ?? e.value) : e);
                    let correctCount = 0;
                    const rows = expected.map((exp, i) => {
                        const u = userVals[i] ?? null;
                        const ok = exp && String(u).trim().toLowerCase() === String(exp).trim().toLowerCase();
                        if (ok) correctCount++;
                        return { userText: u ?? '(chưa)', correctText: exp ?? '(---)', ok };
                    });
                    renderFeedback(qid, `Đúng ${correctCount} / ${expected.length}`, rows, rowBuilders.twoCols);
                }

                function renderPart2(qid, payload) {

                    const questionBlock = document.querySelector(`.question-block[data-qid="${qid}"]`);


                    let meta = null;
                    try {

                        if (questionBlock && questionBlock.dataset.metadata) {
                            meta = JSON.parse(questionBlock.dataset.metadata);
                        }
                    } catch (e) { }


                    meta = meta || window.currentQuestionMeta || {};

                    const sentences = meta.sentences || [];
                    const corr = meta.correct_order || [];
                    const rawOrder = payload.order || [];
                    let correctCount = 0;
                    const rows = sentences.map((s, i) => {
                        const userIdx = rawOrder[i] != null ? Number(rawOrder[i]) : null;
                        const userText = userIdx != null ? sentences[userIdx] : "(chưa)";
                        const corrIdx = corr[i] ?? i;
                        const corrText = sentences[corrIdx] ?? "";
                        const ok = userIdx != null && userIdx === corrIdx;
                        if (ok) correctCount++;
                        return { userText, correctText: corrText, ok };
                    });
                    renderFeedback(qid, `Đúng ${correctCount} / ${sentences.length}`, rows, rowBuilders.twoCols);
                }

                function renderPart3(qid, payload) {
                    const meta = window.currentQuestionMeta ?? {};
                    const items = Array.isArray(meta.items) ? meta.items : [];
                    const options = Array.isArray(meta.options) ? meta.options : [];
                    const answers = Array.isArray(meta.answers) ? meta.answers.map(v => (isNaN(v) ? v : Number(v))) : [];
                    const userArr = payload?.values ?? payload?.selected ?? payload?.value ?? [];

                    let correctCount = 0;
                    const rows = items.map((it, i) => {
                        const raw = (userArr && typeof userArr[i] !== 'undefined') ? userArr[i] : null;
                        const userText = (raw !== null && options[raw] !== undefined) ? options[raw] : (raw !== null ? String(raw) : '(chưa)');
                        const corrRaw = (typeof answers[i] !== 'undefined') ? answers[i] : null;
                        const corrText = (corrRaw !== null && options[corrRaw] !== undefined) ? options[corrRaw] : (corrRaw !== null ? String(corrRaw) : '(---)');
                        const ok = (raw !== null && corrRaw !== null && String(raw) === String(corrRaw));
                        if (ok) correctCount++;
                        return { userText, correctText: corrText, ok };
                    });
                    renderFeedback(qid, `Đúng ${correctCount} / ${items.length}`, rows, rowBuilders.twoCols);
                }

                function renderPart4(qid, payload) {
                    // Prefer per-question metadata found in the DOM (data attributes or hidden meta element).
                    // Fallback order: question block dataset.metadata -> [data-meta-json] element -> payload.meta -> window.currentQuestionMeta
                    let meta = {};
                    try {
                        const qBlock = document.querySelector(`.question-block[data-qid="${qid}"]`);
                        if (qBlock) {
                            if (qBlock.dataset && qBlock.dataset.metadata) {
                                meta = JSON.parse(qBlock.dataset.metadata);
                            } else {
                                const metaEl = qBlock.querySelector('[data-meta-json]');
                                if (metaEl) meta = JSON.parse(metaEl.getAttribute('data-meta-json'));
                            }
                        }
                    } catch (e) {
                        meta = {};
                    }

                    // fallback to payload-provided metadata, then global
                    if ((!meta || Object.keys(meta).length === 0) && payload && payload.meta) meta = payload.meta;
                    if ((!meta || Object.keys(meta).length === 0) && window.currentQuestionMeta) meta = window.currentQuestionMeta;

                    const options = Array.isArray(meta.options) ? meta.options : [];
                    const paragraphs = Array.isArray(meta.paragraphs) ? meta.paragraphs : [];
                    const correct = Array.isArray(meta.correct) ? meta.correct : (Array.isArray(meta.answers) ? meta.answers : []);
                    const userVals = Array.isArray(payload?.value) ? payload.value : (Array.isArray(payload?.selected) ? payload.selected : []);

                    let correctCount = 0;
                    const displayCount = Math.max(paragraphs.length, userVals.length, correct.length);
                    const rows = Array.from({ length: displayCount }).map((_, i) => {
                        const para = paragraphs[i] ?? '';
                        const raw = typeof userVals[i] !== 'undefined' ? userVals[i] : null;
                        const sKey = raw !== null ? String(raw).trim() : '';
                        let userText = '(chưa chọn)';
                        if (sKey !== '') {
                            // try numeric index then fallback to raw string
                            const idx = Number(sKey);
                            if (!Number.isNaN(idx) && typeof options[idx] !== 'undefined') userText = options[idx];
                            else userText = String(raw);
                        }

                        const corrRaw = typeof correct[i] !== 'undefined' ? correct[i] : null;
                        const cKey = corrRaw !== null ? String(corrRaw).trim() : '';
                        let corrText = '';
                        if (cKey !== '') {
                            const cIdx = Number(cKey);
                            if (!Number.isNaN(cIdx) && typeof options[cIdx] !== 'undefined') corrText = options[cIdx];
                            else corrText = String(corrRaw);
                        }

                        const ok = (sKey !== '' && cKey !== '' && String(userText).trim().toLowerCase() === String(corrText).trim().toLowerCase());
                        if (ok) correctCount++;
                        return { para, userText, corrText, ok };
                    });
                    renderFeedback(qid, `Đúng ${correctCount} / ${paragraphs.length}`, rows, rowBuilders.paragraph, 'space-y-4');
                }

                function renderListeningPart1(qid, payload) {

                    const questionBlock = document.querySelector(`.question-block[data-qid="${qid}"]`);


                    let meta = null;
                    try {

                        if (questionBlock && questionBlock.dataset.metadata) {
                            meta = JSON.parse(questionBlock.dataset.metadata);
                        }
                    } catch (e) {
                        console.error('Error parsing metadata:', e);
                    }


                    meta = meta || window.currentQuestionMeta || {};

                    const options = Array.isArray(meta.options) ? meta.options : [];
                    const optionMapping = meta.optionMapping || {};


                    let selectedDisplayIdx = null;
                    if (payload && (typeof payload.value !== 'undefined') && payload.value !== null && payload.value !== '') {
                        selectedDisplayIdx = payload.value;
                    } else if (payload && (typeof payload.selected !== 'undefined') && payload.selected !== null) {
                        selectedDisplayIdx = payload.selected;
                    } else {
                        const radio = questionBlock ? questionBlock.querySelector('input[name="selected_option_id"]:checked') : null;
                        if (radio) selectedDisplayIdx = radio.value;
                    }


                    const selectedOriginalIdx = selectedDisplayIdx !== null && optionMapping[selectedDisplayIdx] !== undefined
                        ? optionMapping[selectedDisplayIdx]
                        : selectedDisplayIdx;



                    const userText = (selectedOriginalIdx !== null && options[selectedOriginalIdx] !== undefined)
                        ? options[selectedOriginalIdx]
                        : (selectedDisplayIdx !== null ? String(selectedDisplayIdx) : '(chưa)');

                    const corrRaw = (typeof meta.correct_index !== 'undefined' && meta.correct_index !== null)
                        ? meta.correct_index
                        : (Array.isArray(meta.correct) ? meta.correct[0] : null);

                    const corrText = (corrRaw !== null && options[corrRaw] !== undefined)
                        ? options[corrRaw]
                        : (corrRaw !== null ? String(corrRaw) : '(---)');


                    const ok = selectedOriginalIdx !== null && corrRaw !== null && String(selectedOriginalIdx) === String(corrRaw);
                    const stem = meta.stem || questionBlock?.querySelector('.prose')?.textContent || '';
                    const rows = [{ para: stem, userText: userText, corrText: corrText, ok }];
                    renderFeedback(qid, `Đúng ${ok ? 1 : 0} / 1`, rows, rowBuilders.paragraph, 'space-y-4');
                }

                function renderListeningPart2(qid, payload) {
                    const questionBlock = document.querySelector(`.question-block[data-qid="${qid}"]`);


                    let meta = null;
                    try {
                        if (questionBlock && questionBlock.dataset.metadata) {
                            meta = JSON.parse(questionBlock.dataset.metadata);
                        }
                    } catch (e) {
                        console.error('Error parsing metadata:', e);
                    }

                    meta = meta || window.currentQuestionMeta || {};

                    const speakers = meta.speakers || [];
                    const options = meta.options || [];
                    const answers = meta.answers || {};
                    const rawOrder = payload.order || [];
                    const optionMapping = meta.optionMapping || {};


                    let correctCount = 0;
                    const rows = [];


                    speakers.forEach((speaker, idx) => {

                        if (!answers.hasOwnProperty(idx)) return;

                        const speakerLabel = speaker.label || `Speaker ${speaker.id || (idx + 1)}`;


                        const selectedDisplayIdx = idx < rawOrder.length ? rawOrder[idx] : null;


                        const selectedOriginalIdx = selectedDisplayIdx !== null && Object.keys(optionMapping).length > 0
                            ? optionMapping[selectedDisplayIdx]
                            : selectedDisplayIdx;


                        const userText = selectedDisplayIdx !== null && options[selectedOriginalIdx] !== undefined
                            ? `${speakerLabel}: ${options[selectedOriginalIdx]}`
                            : `${speakerLabel}: (chưa chọn)`;

                        const corrIdx = answers[idx];
                        const corrText = corrIdx !== null && options[corrIdx] !== undefined
                            ? `${speakerLabel}: ${options[corrIdx]}`
                            : `${speakerLabel}: (---)`;


                        const ok = selectedOriginalIdx !== null && corrIdx !== null && Number(selectedOriginalIdx) === Number(corrIdx);
                        if (ok) correctCount++;

                        rows.push({ userText, correctText: corrText, ok });
                    });

                    const answerCount = Object.keys(answers).length;
                    renderFeedback(qid, `Đúng ${correctCount} / ${answerCount}`, rows, rowBuilders.twoCols);
                }

                function renderListeningPart3(qid, payload) {

                    const questionBlock = document.querySelector(`.question-block[data-qid="${qid}"]`);


                    let meta = null;
                    try {

                        if (questionBlock && questionBlock.dataset.metadata) {
                            meta = JSON.parse(questionBlock.dataset.metadata);
                        }
                    } catch (e) {
                        console.error('Error parsing metadata:', e);
                    }


                    meta = meta || window.currentQuestionMeta || {};

                    const items = Array.isArray(meta.items) ? meta.items : [];
                    const options = Array.isArray(meta.options) ? meta.options : [];
                    const answers = Array.isArray(meta.answers) ? meta.answers.map(v => (isNaN(v) ? v : Number(v))) : [];
                    const userArr = payload?.values ?? payload?.selected ?? payload?.value ?? [];
                    const optionMapping = meta.optionMapping || {};


                    let correctCount = 0;
                    const rows = items.map((item, i) => {
                        const selectedDisplayIdx = (userArr && typeof userArr[i] !== 'undefined') ? userArr[i] : null;

                        const selectedOriginalIdx = selectedDisplayIdx !== null && optionMapping[selectedDisplayIdx] !== undefined
                            ? optionMapping[selectedDisplayIdx]
                            : selectedDisplayIdx;



                        const userText = (selectedOriginalIdx !== null && options[selectedOriginalIdx] !== undefined)
                            ? options[selectedOriginalIdx]
                            : (selectedDisplayIdx !== null ? String(selectedDisplayIdx) : '(chưa)');

                        const corrRaw = (typeof answers[i] !== 'undefined') ? answers[i] : null;
                        const corrText = (corrRaw !== null && options[corrRaw] !== undefined)
                            ? options[corrRaw]
                            : (corrRaw !== null ? String(corrRaw) : '(---)');


                        const ok = (selectedOriginalIdx !== null && corrRaw !== null && String(selectedOriginalIdx) === String(corrRaw));
                        if (ok) correctCount++;
                        return { userText: `${item}: ${userText}`, correctText: `${item}: ${corrText}`, ok };
                    });

                    renderFeedback(qid, `Đúng ${correctCount} / ${items.length}`, rows, rowBuilders.twoCols);
                }

                function renderListeningPart4(qid, payload) {



                    const questionBlock = document.querySelector(`.question-block[data-qid="${qid}"]`);


                    let meta = null;
                    try {

                        if (questionBlock && questionBlock.dataset.metadata) {
                            meta = JSON.parse(questionBlock.dataset.metadata);
                        }
                    } catch (e) {
                        console.error('Error parsing metadata:', e);
                    }


                    meta = meta || window.currentQuestionMeta || {};

                    const options = meta.options || [];
                    const optionMapping = meta.optionMapping || {};
                    const stem = meta.stem || questionBlock?.querySelector('.prose')?.textContent || '';

                    let selectedDisplayIdx = null;
                    if (payload && (typeof payload.value !== 'undefined') && payload.value !== null && payload.value !== '') {
                        selectedDisplayIdx = payload.value;
                    } else if (payload && (typeof payload.selected !== 'undefined') && payload.selected !== null) {
                        selectedDisplayIdx = payload.selected;
                    }

                    const selectedOriginalIdx = selectedDisplayIdx !== null && optionMapping[selectedDisplayIdx] !== undefined
                        ? optionMapping[selectedDisplayIdx]
                        : selectedDisplayIdx;

                    const userText = (selectedOriginalIdx !== null && options[selectedOriginalIdx] !== undefined)
                        ? options[selectedOriginalIdx]
                        : (selectedDisplayIdx !== null ? String(selectedDisplayIdx) : '(chưa)');

                    const corrRaw = (typeof meta.correct_index !== 'undefined' && meta.correct_index !== null)
                        ? meta.correct_index
                        : (Array.isArray(meta.correct) ? meta.correct[0] : null);

                    const corrText = (corrRaw !== null && options[corrRaw] !== undefined)
                        ? options[corrRaw]
                        : (corrRaw !== null ? String(corrRaw) : '(---)');


                    const ok = selectedOriginalIdx !== null && corrRaw !== null && String(selectedOriginalIdx) === String(corrRaw);


                    renderFeedback(qid, `Đúng ${ok ? 1 : 0} / 1`, [{
                        para: stem,
                        userText: userText,
                        corrText: corrText,
                        ok: ok
                    }], rowBuilders.paragraph);
                }

                const listeningRenderers = {
                    part1: renderListeningPart1,
                    part2: renderListeningPart2,
                    part3: renderListeningPart3,
                    part4: renderListeningPart4
                };

                const renderers = { part1: renderPart1, part2: renderPart2, part3: renderPart3, part4: renderPart4 };
                const rootEl = document.querySelector(`.question-block[data-qid="${qid}"]`);
                const selEls = rootEl ? [...rootEl.querySelectorAll('select')] : [];

                const skill = (window.currentQuestionMeta && window.currentQuestionMeta.skill) ? window.currentQuestionMeta.skill : (payload && payload.skill ? payload.skill : null);
                const useListening = skill === 'listening';

                const looksLikePart1 = payload?.part === 'part1' || (payload?.part === 'select' && selEls.length > 1);
                if (!useListening && looksLikePart1) {
                    return renderPart1(qid, payload);
                }

                let p = payload?.part || payload?.__part;


                if (useListening && p === 'choice') {
                    const partNum = (window.currentQuestionMeta && window.currentQuestionMeta.part) ? window.currentQuestionMeta.part : (payload?.__part ?? 1);
                    p = 'part' + partNum;
                }

                const dispatcher = useListening ? listeningRenderers : renderers;


                return dispatcher[p] ? dispatcher[p](qid, payload) : (
                    window.inlineFeedback?.show && window.inlineFeedback.show(qid, JSON.stringify(payload.value ?? '(Chưa có đáp án)'), '', '')
                );
            },
            async navigate(url) {
                if (!url) return;
                this.setLoading(true);
                try {

                    if (typeof schedulePersistAnswers === 'function') schedulePersistAnswers(0);

                    const res = await fetch(url, { credentials: 'same-origin' });
                    if (!res.ok) throw new Error();
                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const newMain = doc.querySelector(mainSelector);
                    const oldMain = document.querySelector(mainSelector);
                    if (newMain && oldMain) {
                        oldMain.replaceWith(newMain);

                    }
                    history.pushState({}, '', url);


                    document.querySelectorAll('[data-feedback-rendered]').forEach(el => {
                        el.removeAttribute('data-feedback-rendered');
                    });


                    window.dispatchEvent(new CustomEvent('aptis:container:replace', { detail: { url } }));
                } catch (e) { location.href = url; }
                finally { this.setLoading(false); }
            }
        };


        window.__aptis_feedbackShownForQid = window.__aptis_feedbackShownForQid || {};

        fnext?.addEventListener('click', async e => {
            e.preventDefault();
            let focusedRoot = null;
            try {
                const lastQid = window.__aptis_lastFocusedQid || null;
                if (lastQid) focusedRoot = document.querySelector(`.question-block[data-qid="${lastQid}"]`);
            } catch (e) { focusedRoot = null; }
            const root = focusedRoot || footer._activeBlock();
            const qid = root?.dataset?.qid || footer.qid();
            const payload = footer.collect(root);
            try { /* debug removed: collected payload */ } catch (err) { /* ignore */ }
            footer.save(qid, payload);

            function hasAnswer(p) {
                if (!p) return false;
                const part = p.part;
                if (part === 'part2') {
                    return Array.isArray(p.order) && p.order.some(v => v !== null && v !== undefined && String(v).trim() !== '');
                }
                if (part === 'part3') {
                    const arr = p.value || p.values || p.selected || [];
                    return Array.isArray(arr) && arr.some(v => v !== null && v !== undefined && String(v).trim() !== '');
                }
                if (part === 'part4') {
                    const arr = Array.isArray(p.value) ? p.value : [];
                    return arr.some(v => v !== null && v !== undefined && String(v).trim() !== '');
                }
                if (part === 'select' || part === 'choice') {
                    const v = p.value;
                    if (Array.isArray(v)) return v.some(x => x !== null && x !== undefined && String(x).trim() !== '');
                    return v !== null && v !== undefined && String(v).trim() !== '';
                }
                if (part === 'text') return p.value && String(p.value).trim() !== '';

                if (Array.isArray(p.value)) return p.value.some(v => v !== null && v !== undefined && String(v).trim() !== '');
                if (p.value) return String(p.value).trim() !== '';
                return false;
            }

            const lbl = document.getElementById('footer-next-label');


            if (!hasAnswer(payload)) {

                const prevText = lbl ? lbl.textContent : null;
                if (lbl) {
                    lbl.textContent = 'Vui lòng chọn đáp án';
                    lbl.classList.add('text-red-600', 'font-bold');
                }
                if (root) {
                    const firstCtl = root.querySelector('input, select, textarea');
                    if (firstCtl) { firstCtl.scrollIntoView({ behavior: 'smooth', block: 'center' }); try { firstCtl.focus(); } catch (e) { } }
                    root.classList.add('ring-2', 'ring-red-400');
                    setTimeout(() => root.classList.remove('ring-2', 'ring-red-400'), 1500);
                }
                setTimeout(() => { if (lbl) { lbl.textContent = prevText; lbl.classList.remove('text-red-600'); } }, 1600);
                return;
            }

            const bladeNext = @json($nextUrl ?? null);

            const mainElForNext = document.querySelector(mainSelector);
            const datasetNext = mainElForNext?.dataset?.nextUrl || null;
            const datasetFinal = mainElForNext?.dataset?.finalUrl || null;
            const nextUrl = bladeNext || datasetNext || window.nextUrl || null;
            const finalUrl = datasetFinal || '{{ route('reading.practice.result', ['attempt' => $attempt->id]) }}';
            const isFinal = !nextUrl;

            const alreadyShown = window.__aptis_feedbackShownForQid[qid] === true;

            if (!alreadyShown) {
                try {
                    if (!payload && window.attemptAnswers && window.attemptAnswers[qid]) payload = window.attemptAnswers[qid];
                } catch(e) {}

                footer.showFeedback(qid, payload);
                window.__aptis_feedbackShownForQid[qid] = true;

                try {
                    const form = document.getElementById('answer-form');
                    const saveUrl = form ? form.action : null;
                    const tokenEl = document.querySelector('input[name="_token"]');
                    const csrf = tokenEl ? tokenEl.value : null;
                    if (saveUrl && csrf) {

                        let metaToSend = payload;
                        try {
                            if (payload && payload.part === 'part1' && Array.isArray(payload.value)) {
                                metaToSend = { selected: payload.value };
                            } else if (payload && payload.part === 'select' && Array.isArray(payload.value)) {
                                metaToSend = { selected: payload.value };
                            } else if (payload && payload.value !== undefined) {

                                metaToSend = { selected: payload.value };
                            }
                        } catch (e) { metaToSend = payload; }

                        fetch(saveUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ action: 'submit', metadata: metaToSend, client_provided: true })
                        }).then(r => r.json()).then(resp => {}).catch(() => {});
                    }
                } catch (e) { }
                if (lbl) lbl.textContent = isFinal ? 'Hoàn thành' : 'Next';
                return;
            }

            // lần 2 mới đi tiếp
            if (isFinal) {
                location.href = finalUrl;
            } else {
                footer.navigate(nextUrl);
            }
        });

        // Avoid forcing a full reload on history popstate which can cause the SPA to jump
        // to the result page unexpectedly. Instead emit a custom event so page-level
        // code can decide whether to re-fetch or re-init the container.
        window.addEventListener('popstate', (ev) => {
            window.dispatchEvent(new CustomEvent('aptis:history:pop', { detail: ev }));
        });
    })();
</script>