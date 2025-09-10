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

    <form id="answer-form" method="POST" action="{{ route('listening.practice.answer', ['attempt' => $attempt->id, 'question' => $question->id]) }}">
            @csrf
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

            <div id="inline-feedback" class="mt-3 p-3 rounded hidden"></div>

            <div class="flex items-center justify-between mt-6">
                <div class="space-x-2">
                        @if($previousPosition)
                        <a href="{{ route('listening.practice.question', ['attempt' => $attempt->id, 'position' => $previousPosition]) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">&larr; Trước</a>
                    @endif

                    @if($nextPosition)
                        <a href="{{ route('listening.practice.question', ['attempt' => $attempt->id, 'position' => $nextPosition]) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Tiếp &rarr;</a>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" id="save-local-btn" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Lưu</button>
                    <button type="button" id="final-submit-btn" class="btn-base btn-primary">Nộp bài (Cuối)</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function(){
    const form = document.getElementById('answer-form');
    const submitBtn = document.getElementById('submit-btn');

    function collectMeta() {
        const fd = {};
        // radio
        const opt = form.querySelector('input[type=radio]:checked');
        if (opt) fd.selected = opt.value;

        // selects for speakers
        const selects = form.querySelectorAll('select[name^="metadata[selected]"]');
        if (selects && selects.length) {
            fd.selected = {};
            selects.forEach((s, idx) => { fd.selected[idx] = s.value; });
        }
        return fd;
    }
    // Basic client-side grader for this question. It mirrors the server's gradeAnswer for common types.
    function gradeLocal(selected, meta) {
        try {
            const part = meta.part || null;
            // MC questions
            if (([1,16,17].includes(part)) || meta.type === 'mc' || Array.isArray(meta.options)) {
                const correctIndex = meta.correct_index ?? meta.correct ?? null;
                const sel = selected;
                const isCorrect = (correctIndex !== null) && (String(sel) === String(correctIndex));
                return { is_correct: isCorrect, correct: correctIndex };
            }

            // speaker/list mapping
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
        } catch (e) {
            // fallthrough
        }
        return { is_correct: false, correct: null };
    }

    // ensure global answer store and try to restore from localStorage
    window.attemptAnswers = window.attemptAnswers || {};
    try {
        const saved = localStorage.getItem('attempt_answers_{{ $attempt->id }}');
        if (saved) {
            const parsed = JSON.parse(saved);
            if (parsed && typeof parsed === 'object') {
                window.attemptAnswers = Object.assign({}, parsed, window.attemptAnswers);
            }
        }
    } catch (e) { /* ignore parse errors */ }

    const questionId = {{ $question->id }};
    const qMeta = {!! json_encode($question->metadata ?? []) !!};

    const saveLocalBtn = document.getElementById('save-local-btn');
    const finalSubmitBtn = document.getElementById('final-submit-btn');

    function saveLocal() {
        const meta = collectMeta();
        const grading = gradeLocal(meta.selected ?? meta.selected_option_id ?? meta.selected, qMeta);
        window.attemptAnswers[questionId] = { selected: meta.selected ?? meta.selected_option_id ?? meta.option_id ?? null, is_correct: grading.is_correct };

    // persist to localStorage keyed by attempt id so it survives navigation
    try { localStorage.setItem('attempt_answers_{{ $attempt->id }}', JSON.stringify(window.attemptAnswers)); } catch (e) { /* ignore */ }

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

    // summary feature removed

    finalSubmitBtn && finalSubmitBtn.addEventListener('click', function(){
        if (!confirm('Bạn chắc chắn muốn nộp bài? Sau khi nộp sẽ không thể thay đổi.')) return;
        finalSubmitBtn.disabled = true;
        const batchUrl = '{{ route('listening.practice.batchSubmit', $attempt->id) }}';
    // ensure latest saved locally
    try { localStorage.setItem('attempt_answers_{{ $attempt->id }}', JSON.stringify(window.attemptAnswers)); } catch (e) {}

    fetch(batchUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value },
            body: JSON.stringify({ answers: window.attemptAnswers, final: true })
        }).then(r => r.json()).then(resp => {
            finalSubmitBtn.disabled = false;
            if (resp.success) {
        // clear local storage on final submit success
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
