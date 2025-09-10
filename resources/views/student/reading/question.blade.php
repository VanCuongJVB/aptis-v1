@extends('layouts.app')

@section('title', 'Câu hỏi Reading')

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
            {{-- Question content (single question) --}}
            @if(isset($question))
                <div class="prose">{!! $question->content ?? $question->title !!}</div>
            @endif
        </div>

        @php
            // defensive defaults to avoid undefined variable errors
            $allQuestions = $allQuestions ?? null;
            $previousPosition = $previousPosition ?? null;
            $nextPosition = $nextPosition ?? null;
            $answer = $answer ?? null;
        @endphp

        @php
            // determine form action: if full-part, post to first question's answer route to leverage existing action=finish handling
            if (isset($allQuestions) && $allQuestions->isNotEmpty()) {
                $firstQ = $allQuestions->first();
                $formAction = route('reading.practice.answer', ['attempt' => $attempt->id, 'question' => $firstQ->id]);
            } else {
                $formAction = route('reading.practice.answer', ['attempt' => $attempt->id, 'question' => $question->id]);
            }
        @endphp

        <form id="answer-form" method="POST" action="{{ $formAction }}">
            @csrf
            <input type="hidden" name="_method" value="POST">
            <div class="space-y-3 mb-4">
                @if(isset($allQuestions) && $allQuestions->isNotEmpty())
                    {{-- Full-part: render every question block --}}
                    @foreach($allQuestions as $qIdx => $q)
                        @php $ansForQ = $answersMap->get($q->id) ?? null; @endphp
                        <div class="mb-6 question-block" data-qid="{{ $q->id }}">
                            <div class="prose mb-3">{!! $q->content ?? $q->title !!}</div>
                            @php $part = $q->part ?? $q->metadata['part'] ?? $quiz->part; @endphp
                            @includeWhen(true, 'student.reading.parts.part' . $part, [
                                'question' => $q,
                                'answer' => $ansForQ
                            ])
                        </div>
                    @endforeach
                @else
                    @php
                        $part = $question->part ?? $question->metadata['part'] ?? $quiz->part;
                    @endphp
                    @includeWhen(true, 'student.reading.parts.part' . $part, [
                        'question' => $question,
                        'answer' => $answer ?? null
                    ])
                @endif
            </div>

            <div class="flex items-center justify-between">
                <div class="space-x-2">
                    @if(!$allQuestions && $previousPosition)
                        <a href="{{ route('reading.practice.question', ['attempt' => $attempt->id, 'position' => $previousPosition]) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">&larr; Trước</a>
                    @endif

                    @if(!$allQuestions && $nextPosition)
                        <a href="{{ route('reading.practice.question', ['attempt' => $attempt->id, 'position' => $nextPosition]) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Tiếp &rarr;</a>
                    @endif
                </div>

                    <div class="flex items-center space-x-2">
                        <button type="button" id="save-local-btn" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Lưu</button>
                        <button type="button" id="final-submit-btn" class="btn-base btn-primary">Nộp bài (Cuối)</button>
                    </div>
            </div>
        </form>
    </div>
</div>

    <div id="inline-feedback" class="hidden"></div>

@if($attempt->metadata['mode'] ?? 'learning' === 'learning')
    @push('scripts')
    <script>
    (function(){
        const form = document.getElementById('answer-form');

        function collectMeta() {
            const fd = {};
            const opt = form.querySelector('input[type=radio]:checked');
            if (opt) fd.selected = opt.value;

            const selects = form.querySelectorAll('select[name^="metadata[selected]"]');
            if (selects && selects.length) {
                fd.selected = {};
                selects.forEach((s, idx) => { fd.selected[idx] = s.value; });
            }
            return fd;
        }

        function gradeLocal(selected, meta) {
            try {
                const part = meta.part || null;
                if (([1,16,17].includes(part)) || meta.type === 'mc' || Array.isArray(meta.options)) {
                    const correctIndex = meta.correct_index ?? meta.correct ?? null;
                    const sel = selected;
                    const isCorrect = (correctIndex !== null) && (String(sel) === String(correctIndex));
                    return { is_correct: isCorrect, correct: correctIndex };
                }

                if (part === 14 || meta.type === 'speakers' || meta.type === 'listening_speakers_complete') {
                    const correct = meta.answers || [];
                    const sel = selected || {};
                    const isCorrect = JSON.stringify(Object.values(sel)) === JSON.stringify(Object.values(correct));
                    return { is_correct: isCorrect, correct };
                }

                if (part === 15 || meta.type === 'who_expresses') {
                    const correct = meta.answers || [];
                    const sel = selected || {};
                    const isCorrect = JSON.stringify(Object.values(sel)) === JSON.stringify(Object.values(correct));
                    return { is_correct: isCorrect, correct };
                }
            } catch (e) {}
            return { is_correct: false, correct: null };
        }

        window.attemptAnswers = window.attemptAnswers || {};
        try {
            const saved = localStorage.getItem('attempt_answers_{{ $attempt->id }}');
            if (saved) {
                const parsed = JSON.parse(saved);
                if (parsed && typeof parsed === 'object') {
                    window.attemptAnswers = Object.assign({}, parsed, window.attemptAnswers);
                }
            }
        } catch (e) {}

        const questionId = {{ $question->id ?? 'null' }};
        const qMeta = {!! json_encode($question->metadata ?? []) !!};

        const saveLocalBtn = document.getElementById('save-local-btn');
        const finalSubmitBtn = document.getElementById('final-submit-btn');

        // Restore saved answers from window.attemptAnswers into inputs
        function restoreSavedAnswers() {
            try {
                if (!window.attemptAnswers) return;
                // If full-part, populate each .question-block
                const blocks = Array.from(document.querySelectorAll('.question-block'));
                if (blocks.length) {
                    blocks.forEach(block => {
                        const qid = block.getAttribute('data-qid');
                        const saved = window.attemptAnswers[qid];
                        if (!saved) return;
                        // handle radio
                        if (saved.selected !== undefined && saved.selected !== null) {
                            const radio = block.querySelector('input[type=radio][value="' + saved.selected + '"]');
                            if (radio) radio.checked = true;
                        }
                        // handle selects
                        const selects = block.querySelectorAll('select');
                        selects.forEach((s, idx) => {
                            const nameKey = s.name || ('selected_' + idx);
                            if (saved.selected && typeof saved.selected === 'object') {
                                const v = saved.selected[idx] ?? saved.selected[nameKey] ?? null;
                                if (v !== null && v !== undefined) s.value = v;
                            }
                        });
                    });
                    return;
                }

                // Single-question mode
                if (questionId && window.attemptAnswers[questionId]) {
                    const saved = window.attemptAnswers[questionId];
                    if (saved.selected !== undefined && saved.selected !== null) {
                        const radio = document.querySelector('input[type=radio][value="' + saved.selected + '"]');
                        if (radio) radio.checked = true;
                        const sel = document.querySelector('select');
                        if (sel && (sel.name && typeof saved.selected === 'object')) {
                            // try mapping by index
                            Object.keys(saved.selected).forEach(k => {
                                const s = document.querySelector('select[name="metadata[selected][' + k + ']"]');
                                if (s) s.value = saved.selected[k];
                            });
                        }
                    }
                }
            } catch (e) {
                console.error('restoreSavedAnswers error', e);
            }
        }

        // Immediately restore on load
        restoreSavedAnswers();

        function saveLocal() {
            const meta = collectMeta();
            const grading = gradeLocal(meta.selected ?? meta.selected_option_id ?? meta.selected, qMeta);
            if (questionId) {
                window.attemptAnswers[questionId] = { selected: meta.selected ?? meta.selected_option_id ?? meta.option_id ?? null, is_correct: grading.is_correct };
            }
            try { localStorage.setItem('attempt_answers_{{ $attempt->id }}', JSON.stringify(window.attemptAnswers)); } catch (e) {}

            const fb = document.getElementById('inline-feedback');
            fb.classList.remove('hidden');
            fb.classList.remove('bg-green-50','border-green-200','text-green-800','bg-red-50','border-red-200','text-red-800');
            if (grading.is_correct) {
                fb.classList.add('bg-green-50','border','border-green-200','text-green-800');
                fb.innerText = 'Đã lưu (Đúng)';
            } else {
                fb.classList.add('bg-red-50','border','border-red-200','text-red-800');
                fb.innerText = 'Đã lưu (Sai)';
            }
        }

        saveLocalBtn && saveLocalBtn.addEventListener('click', function(){ saveLocal(); });

        finalSubmitBtn && finalSubmitBtn.addEventListener('click', function(){
            if (!confirm('Bạn chắc chắn muốn nộp bài? Sau khi nộp sẽ không thể thay đổi.')) return;
            finalSubmitBtn.disabled = true;
            const batchUrl = '{{ route('reading.practice.batchSubmit', $attempt->id) }}';
            try { localStorage.setItem('attempt_answers_{{ $attempt->id }}', JSON.stringify(window.attemptAnswers)); } catch (e) {}

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
@endif

@endsection
