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
        const fnext = document.getElementById('footer-next-btn');
        const spinner = document.getElementById('footer-next-spinner');
        const mainSelector = '.container.mx-auto';

        const footer = {
            setLoading(on) { spinner?.classList.toggle('hidden', !on); fnext.disabled = on; },
            qid() { return document.querySelector('.question-block')?.dataset.qid || null; },
            collect(root) {
                if (!root) return null;
                if (root.querySelector('.slot')) {
                    const order = [], texts = [];
                    root.querySelectorAll('.slot').forEach(s => {
                        const it = s.querySelector('.draggable-item');
                        if (it) { order.push(it.dataset.index); texts.push(it.innerText.trim()); }
                        else order.push(null);
                    });
                    return { part: 'part2', order, texts };
                }

                const checked = root.querySelectorAll('input:checked');
                if (checked.length) return { part: 'choice', value: Array.from(checked).map(i => i.value) };

                // detect multiple selects (Part 4 style) and return array of values
                const selEls = root.querySelectorAll('select');
                if (selEls.length > 1) {
                    return { part: 'part4', value: Array.from(selEls).map(s => s.value) };
                }

                if (selEls.length === 1) return { part: 'select', value: selEls[0].value };

                const ta = root.querySelector('textarea'); if (ta) return { part: 'text', value: ta.value };
                return null;
            },
            save(qid, payload) {
                if (!qid) return;
                window.attemptAnswers = window.attemptAnswers || {};
                window.attemptAnswers[qid] = payload;
                try { localStorage.setItem('attempt_answers_' + window.currentAttemptId, JSON.stringify(window.attemptAnswers)); } catch (e) { }
            },
            showFeedback(qid, payload) {
                if (!payload) return;
                // debug logging removed in production

                function renderFeedback(qid, stats, rows, rowRenderer, space = 'space-y-2') {
                    const target = document.querySelector(`.inline-feedback[data-qid-feedback="${qid}"]`);
                    if (!target) return;
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
                        userCol.className = 'text-sm text-gray-800 flex items-center gap-2';
                        if (r.ok) {
                            userCol.innerHTML = `
                                <svg class="h-4 w-4 text-green-600 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414-1.414L8 11.172 4.707 7.879a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8z" clip-rule="evenodd"/></svg>
                                <span>${r.userText}</span>
                            `;
                        } else {
                            userCol.textContent = r.userText;
                        }
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

                function renderPart1(qid, payload) {
                    const selEls = [...document.querySelectorAll(`.question-block[data-qid="${qid}"] select`)];
                    const userVals = selEls.map(s => s.value || null);
                    const meta = window.currentQuestionMeta ?? {};
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
                    const meta = window.currentQuestionMeta ?? {};
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
                    if (window.inlineFeedback?.show) {
                        const userText = JSON.stringify(payload.selected ?? payload.value ?? '(Chưa có đáp án)');
                        window.inlineFeedback.show(qid, userText, '', '');
                    }
                }

                function renderPart4(qid, payload) {
                    const meta = window.currentQuestionMeta ?? {};
                    const options = meta.options || [];
                    const paragraphs = meta.paragraphs || [];
                    const correct = meta.correct || [];
                    const userVals = payload.value || [];
                    let correctCount = 0;
                    const rows = paragraphs.map((para, i) => {
                        const raw = typeof userVals[i] !== 'undefined' ? userVals[i] : null;
                        const userIdx = (raw !== null && String(raw).trim() !== '') ? Number(raw) : null;
                        const userText = userIdx !== null ? options[userIdx] : "(chưa chọn)";
                        const corrRaw = typeof correct[i] !== 'undefined' ? correct[i] : null;
                        const corrIdx = (corrRaw !== null && String(corrRaw).trim() !== '') ? Number(corrRaw) : null;
                        const corrText = corrIdx !== null ? options[corrIdx] : "";
                        const ok = userIdx !== null && corrIdx !== null && userIdx === corrIdx;
                        if (ok) correctCount++;
                        return { para, userText, corrText, ok };
                    });
                    renderFeedback(qid, `Đúng ${correctCount} / ${paragraphs.length}`, rows, rowBuilders.paragraph, 'space-y-4');
                }

                const renderers = { part1: renderPart1, part2: renderPart2, part3: renderPart3, part4: renderPart4 };
                const rootEl = document.querySelector(`.question-block[data-qid="${qid}"]`);
                const selEls = rootEl ? [...rootEl.querySelectorAll('select')] : [];
                const looksLikePart1 = payload?.part === 'part1' || (payload?.part === 'select' && selEls.length > 1);
                if (looksLikePart1) return renderPart1(qid, payload);

                const p = payload?.part || payload?.__part;
                // renderer dispatch - debug suppressed
                return renderers[p] ? renderers[p](qid, payload) : (
                    window.inlineFeedback?.show && window.inlineFeedback.show(qid, JSON.stringify(payload.value ?? '(Chưa có đáp án)'), '', '')
                );
            },
            async navigate(url) {
                if (!url) return;
                this.setLoading(true);
                try {
                    const res = await fetch(url, { credentials: 'same-origin' });
                    if (!res.ok) throw new Error();
                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const newMain = doc.querySelector(mainSelector);
                    const oldMain = document.querySelector(mainSelector);
                    if (newMain && oldMain) oldMain.replaceWith(newMain);
                    history.pushState({}, '', url);
                    doc.querySelectorAll('script:not([src])').forEach(s => {
                        const ns = document.createElement('script');
                        ns.textContent = s.textContent; document.body.appendChild(ns);
                    });
                } catch (e) { location.href = url; }
                finally { this.setLoading(false); }
            }
        };

        let feedbackShownForQid = {};

        fnext?.addEventListener('click', async e => {
            e.preventDefault();

            const root = document.querySelector('.question-block');
            const qid = footer.qid();
            const payload = footer.collect(root);
            footer.save(qid, payload);

            const bladeNext = @json($nextUrl ?? null);
            const nextUrl = window.nextUrl || bladeNext;
            const finalUrl = '{{ route('reading.practice.result', ['attempt' => $attempt->id]) }}';
            const isFinal = !nextUrl;
            const lbl = document.getElementById('footer-next-label');

            const alreadyShown = feedbackShownForQid[qid] === true;

            if (!alreadyShown) {
                footer.showFeedback(qid, payload);
                feedbackShownForQid[qid] = true;
                if (lbl) lbl.textContent = isFinal ? 'Hoàn thành' : 'Next';
                return; // ✅ lần đầu chỉ show feedback, không đi đâu cả
            }

            // ✅ lần 2 mới đi tiếp
            if (isFinal) {
                location.href = finalUrl;
            } else {
                footer.navigate(nextUrl);
            }


        });

        window.addEventListener('popstate', () => location.reload());
    })();
</script>