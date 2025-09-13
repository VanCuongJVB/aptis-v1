@extends('layouts.app')

@section('title', 'Câu hỏi Listening')

@section('content')
<div class="container mx-auto py-6">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-lg font-semibold">{{ $quiz->title }} — Câu {{ $position }} / {{ $total }}</h2>
                <p class="text-sm text-gray-500">Part {{ $quiz->part }}</p>
            </div>
            <div class="text-sm text-gray-600"><span>{{ $attempt->started_at ? $attempt->started_at->format('H:i d/m/Y') : '' }}</span></div>
        </div>

        <div class="mb-4">
            {{-- Audio area: show player if file exists, otherwise placeholder icon --}}
            @php
                $audio = $question->metadata['audio_path'] ?? null;
                $audio_text = $question->metadata['audio_text'] ?? null;
            @endphp

            @if($audio)
                <audio controls class="w-full mb-2">
                    <source src="{{ asset($audio) }}" type="audio/mpeg">
                    {{ __('Your browser does not support the audio element.') }}
                </audio>
            @else
                <div class="flex items-center gap-3 p-3 border rounded bg-gray-50 mb-2">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-100 text-blue-600 text-lg">🎧</div>
                    <div class="text-sm text-gray-600">Không có file âm thanh cho câu hỏi này.</div>
                </div>
            @endif

            @if($audio_text)
                <div class="prose text-sm text-gray-700 mb-3">{!! nl2br(e($audio_text)) !!}</div>
            @endif
        </div>

    <form id="answer-form" method="POST" action="{{ route('listening.practice.answer', ['attempt' => $attempt->id, 'question' => $question->id]) }}" data-qid="{{ $question->id }}">
            @csrf
            <div class="question-block" data-qid="{{ $question->id }}">
                <div class="prose mb-4">{!! $question->content ?? $question->title !!}</div>

                <div id="question-body">
                @php $meta = $question->metadata ?? []; $type = $meta['type'] ?? 'mc'; @endphp
                @if($type === 'mc' || isset($meta['options']))
                    <div class="space-y-2">
                        @foreach($meta['options'] as $idx => $opt)
                            <label class="block border rounded p-3 cursor-pointer">
                                <input type="radio" name="selected_option_id" value="{{ $idx }}" class="mr-2 option-input" {{ (isset($answer) && $answer->selected_option_id == $idx) ? 'checked' : '' }}>
                                {!! $opt !!}
                            </label>
                        @endforeach
                    </div>
                @elseif($meta['type'] === 'listening_speakers_complete')
                    {{-- render simple selects for speakers complete --}}
                    <div class="space-y-2">
                        @foreach($meta['items'] as $i => $it)
                            <div class="flex items-center space-x-2">
                                <div class="w-2/3">{!! $it !!}</div>
                                <div class="w-1/3">
                                    <select name="metadata[selected][{{ $i }}]" class="w-full border rounded p-2">
                                        <option value="">Chọn người nói</option>
                                        @foreach($meta['speakers'] as $spIdx => $sp)
                                            <option value="{{ $spIdx }}">{{ $sp }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-gray-600">Loại câu hỏi chưa được hỗ trợ trong giao diện này.</div>
                @endif
            </div>

            </div> <!-- /.question-block -->

            <div id="inline-feedback" class="inline-feedback mt-3 p-3 rounded hidden" data-qid-feedback="{{ $question->id }}"></div>

            <div class="flex items-center justify-between mt-6">
                <div class="space-x-2">
                        @if($previousPosition)
                        <a href="{{ route('listening.practice.question', ['attempt' => $attempt->id, 'position' => $previousPosition]) }}" data-position="{{ $previousPosition }}" data-url="{{ route('listening.practice.question', ['attempt' => $attempt->id, 'position' => $previousPosition]) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">&larr; Trước</a>
                    @endif

                    @if($nextPosition)
                        <a href="{{ route('listening.practice.question', ['attempt' => $attempt->id, 'position' => $nextPosition]) }}" data-position="{{ $nextPosition }}" data-url="{{ route('listening.practice.question', ['attempt' => $attempt->id, 'position' => $nextPosition]) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Tiếp &rarr;</a>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" id="final-submit-btn" class="btn-base btn-primary">Nộp bài (Cuối)</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function(){
    // Client-side single-load navigation: when the controller provides `allQuestions`, we'll
    // render questions in-memory and avoid full page reloads. Otherwise fallback to existing behavior.

    const form = document.getElementById('answer-form');
    const finalSubmitBtn = document.getElementById('final-submit-btn');

    // restore locally saved answers
    window.attemptAnswers = window.attemptAnswers || {};
    try {
        const saved = localStorage.getItem('attempt_answers_{{ $attempt->id }}');
        if (saved) {
            const parsed = JSON.parse(saved);
            if (parsed && typeof parsed === 'object') window.attemptAnswers = Object.assign({}, parsed, window.attemptAnswers);
        }
    } catch (e) { /* ignore */ }

    const initialPayload = {!! json_encode($initialPayload ?? null) !!};
    const fullQuestions = {!! isset($allQuestions) ? json_encode($allQuestions->map(function($q){ return ['id'=>$q->id,'content'=>$q->content ?? $q->title,'title'=>$q->title,'metadata'=>$q->metadata ?? [],'audio'=>$q->metadata['audio_path'] ?? null,'audio_text'=>$q->metadata['audio_text'] ?? null]; })->values()) : 'null' !!};

    // Elements we update
    const titleEl = document.querySelector('.flex.justify-between.items-start h2');
    const partEl = document.querySelector('.flex.justify-between.items-start p');
    const audioContainer = document.querySelector('.mb-4'); // parent of audio area
    const questionContentEl = document.querySelector('.prose.mb-4');
    const questionBodyEl = document.getElementById('question-body');
    const inlineFeedback = document.getElementById('inline-feedback');

    // Utilities
    function saveLocal() {
        try { localStorage.setItem('attempt_answers_{{ $attempt->id }}', JSON.stringify(window.attemptAnswers)); } catch (e) {}
    }

    function renderAudio(meta) {
        if (!audioContainer) return;
        let html = '';
        if (meta && meta.audio) {
            html += `<audio controls class="w-full mb-2"><source src="${meta.audio}" type="audio/mpeg">Trình duyệt của bạn không hỗ trợ phần tử audio.</audio>`;
        } else if (meta && meta.audio_text) {
            html += `<div class="prose text-sm text-gray-700 mb-3">${meta.audio_text.replace(/\n/g, '<br>')}</div>`;
        } else {
            html += audioContainer.querySelector('audio') ? '' : '<div class="flex items-center gap-3 p-3 border rounded bg-gray-50 mb-2"><div class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-100 text-blue-600 text-lg">🎧</div><div class="text-sm text-gray-600">Không có file âm thanh cho câu hỏi này.</div></div>';
        }
        // replace only the first child area that previously held audio/placeholder
        const firstChild = audioContainer.querySelector('audio, .flex.items-center') || audioContainer.firstElementChild;
        if (firstChild) firstChild.outerHTML = html; else audioContainer.insertAdjacentHTML('afterbegin', html);
    }

    function renderQuestion(idx) {
        if (!Array.isArray(window.questions) || !window.questions[idx]) return;
        const q = window.questions[idx];
        const pos = idx + 1;
        const total = window.questions.length;

        // update header
        if (titleEl) titleEl.textContent = `${'{{ $quiz->title }}'} — Câu ${pos} / ${total}`;
        if (partEl) partEl.textContent = `Part ${q.metadata.part ?? q.part ?? '{{ $quiz->part }}'}`;

        // audio
        renderAudio(q);

        // content
        if (questionContentEl) questionContentEl.innerHTML = q.content || q.title || '';

        // body (options / selects)
        if (!questionBodyEl) return;
        const meta = q.metadata || {};
        const type = meta.type || 'mc';
        let bodyHtml = '';

        if (type === 'mc' || Array.isArray(meta.options) && meta.options.length) {
            bodyHtml += '<div class="space-y-2">';
            (meta.options || []).forEach((opt, i) => {
                const checked = (window.attemptAnswers[q.id] && String(window.attemptAnswers[q.id].selected) === String(i)) ? 'checked' : '';
                bodyHtml += `<label class="block border rounded p-3 cursor-pointer"><input type="radio" name="selected_option_id" value="${i}" class="mr-2 option-input" ${checked}>${opt}</label>`;
            });
            bodyHtml += '</div>';
        } else if (meta.type === 'listening_speakers_complete') {
            bodyHtml += '<div class="space-y-2">';
            (meta.items || []).forEach((it, i) => {
                bodyHtml += '<div class="flex items-center space-x-2">';
                bodyHtml += `<div class="w-2/3">${it}</div>`;
                bodyHtml += '<div class="w-1/3">';
                bodyHtml += `<select name="metadata[selected][${i}]" class="w-full border rounded p-2"><option value="">Chọn người nói</option>`;
                (meta.speakers || []).forEach((sp, spIdx) => {
                    const selVal = window.attemptAnswers[q.id] && window.attemptAnswers[q.id].selected && window.attemptAnswers[q.id].selected[i];
                    const sel = String(selVal) === String(spIdx) ? 'selected' : '';
                    bodyHtml += `<option value="${spIdx}" ${sel}>${sp}</option>`;
                });
                bodyHtml += '</select></div></div>';
            });
            bodyHtml += '</div>';
        } else {
            bodyHtml = '<div class="text-gray-600">Loại câu hỏi chưa được hỗ trợ trong giao diện này.</div>';
        }

        questionBodyEl.innerHTML = bodyHtml;

        // attach change listener for saving
        questionBodyEl.querySelectorAll('input, select').forEach(el => {
            el.addEventListener('change', function(){
                // collect
                const entry = {};
                const radio = questionBodyEl.querySelector('input[type=radio]:checked');
                if (radio) entry.selected = radio.value;
                const selects = questionBodyEl.querySelectorAll('select[name^="metadata[selected]"]');
                if (selects && selects.length) {
                    entry.selected = entry.selected || {};
                    selects.forEach((s, i) => entry.selected[i] = s.value);
                }
                window.questions[idx]._lastSaved = Date.now();
                window.attemptAnswers[q.id] = entry;
                saveLocal();
            });
        });

        // update navigation links to store positions in data attributes
        const prevLink = Array.from(document.querySelectorAll('a')).find(a => /Trước|Prev|←|\u2190/.test(a.textContent));
        const nextLink = Array.from(document.querySelectorAll('a')).find(a => /Tiếp|Next|→|\u2192/.test(a.textContent));
        if (prevLink) {
            prevLink.dataset.pos = pos - 1;
            prevLink.dataset.position = pos - 1;
            prevLink.dataset.url = prevLink.href;
            prevLink.addEventListener('click', navClickHandler);
        }
        if (nextLink) {
            nextLink.dataset.pos = pos + 1;
            nextLink.dataset.position = pos + 1;
            nextLink.dataset.url = nextLink.href;
            nextLink.addEventListener('click', navClickHandler);
        }
        // expose prev/next positions and urls for footer controls
        try {
            window.prevPosition = pos - 1;
            window.nextPosition = pos + 1;
            window.prevUrl = prevLink ? prevLink.href : null;
            window.nextUrl = nextLink ? nextLink.href : null;
        } catch(e) {}
        // export some globals for footer and other scripts to use
        try {
            window.currentQuestionIndex = idx;
            window.currentPosition = pos;
            window.total = total;
            window.currentQuestionId = q.id;
            window.currentQuestionMeta = q.metadata || {};
            window.questionId = q.id;
            if (form) form.dataset.qid = q.id;
            // update inline feedback binding for SPA: set data-qid-feedback on the feedback container and clear previous
            try {
                if (inlineFeedback) {
                    inlineFeedback.setAttribute('data-qid-feedback', q.id);
                    inlineFeedback.classList.add('hidden');
                    inlineFeedback.innerHTML = '';
                }
            } catch(e){}
        } catch (e) {}
    }

    // make renderQuestion available globally for footer controls
    try { window.renderQuestionAt = function(i){ return renderQuestion(i); }; } catch (e) {}

    function navClickHandler(e) {
        e.preventDefault();
        const target = e.currentTarget;
        const pos = parseInt(target.dataset.pos || target.getAttribute('data-pos')) || 0;
        if (!window.questions || pos < 1 || pos > window.questions.length) {
            // fallback to normal navigation if out-of-range
            window.location.href = target.href;
            return;
        }
        renderQuestion(pos - 1);
        // update browser history for nicer UX
        try { history.replaceState({}, '', target.href); } catch (e) {}
    }

    // initialize SPA if fullQuestions available
    if (Array.isArray(fullQuestions) && fullQuestions.length) {
        // normalize audio paths: when server sent relative asset paths, ensure they're absolute
        window.questions = fullQuestions.map(q => ({
            id: q.id,
            content: q.content,
            title: q.title,
            metadata: q.metadata || {},
            audio: q.audio ? ('{{ asset('') }}'.replace(/\/g,'/') + q.audio.replace(/^\//, '')) : null
        }));

        const startIndex = {{ isset($position) ? ($position - 1) : 0 }};
        // expose batch submit URL and attempt id for footer
        try { window.batchSubmitUrl = '{{ route('listening.practice.batchSubmit', $attempt->id) }}'; } catch(e){}
        try { window.currentAttemptId = '{{ $attempt->id }}'; } catch(e){}
        renderQuestion(startIndex);
    }

    // Expose current question meta and id for non-SPA mode so helpers can bind and show feedback
    try {
        window.currentQuestionMeta = {!! json_encode($question->metadata ?? []) !!};
        window.questionId = {{ $question->id }};
    } catch(e) {}

    // final submit behavior: reuse existing batch submit route
    finalSubmitBtn && finalSubmitBtn.addEventListener('click', function(){
        if (!confirm('Bạn chắc chắn muốn nộp bài? Sau khi nộp sẽ không thể thay đổi.')) return;
        finalSubmitBtn.disabled = true;
        const batchUrl = '{{ route('listening.practice.batchSubmit', $attempt->id) }}';
        saveLocal();
        fetch(batchUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value },
            body: JSON.stringify({ answers: window.attemptAnswers, final: true })
        }).then(r => r.json()).then(resp => {
            finalSubmitBtn.disabled = false;
            if (resp.success) {
                try { localStorage.removeItem('attempt_answers_{{ $attempt->id }}'); } catch (e) {}
                if (resp.redirect) window.location.href = resp.redirect; else alert('Đã nộp.');
            } else {
                alert(resp.message || 'Lỗi khi nộp bài');
            }
        }).catch(err => { console.error(err); finalSubmitBtn.disabled = false; alert('Lỗi mạng'); });
    });

})();
</script>
@endpush

@endsection

@includeWhen(true, 'student.parts.question-footer')

@push('scripts')
<script>
(function(){
    const fnext = document.getElementById('footer-next-btn');
    if (!fnext) return;
    fnext.addEventListener('click', function(){
        const nextLink = document.querySelector('a[href][class*="Tiếp"][href]') || Array.from(document.querySelectorAll('a')).find(a => /Tiếp|Next|→|\u2192/.test(a.textContent));
        if (nextLink) nextLink.click();
    });
})();
</script>
@endpush
