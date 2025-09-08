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
            {{-- Question content --}}
            <div class="prose">
                {!! $question->content ?? $question->title !!}
            </div>
        </div>

        <form id="answer-form" method="POST" action="{{ route('reading.practice.answer', ['attempt' => $attempt->id, 'question' => $question->id]) }}">
            @csrf
            <input type="hidden" name="_method" value="POST">
            <div class="space-y-3 mb-4">
                @php
                    $part = $question->part ?? $question->metadata['part'] ?? $quiz->part;
                @endphp

                @includeWhen(true, 'student.reading.parts.part' . $part, [
                    'question' => $question,
                    'answer' => $answer
                ])
            </div>

            <div class="flex items-center justify-between">
                <div class="space-x-2">
                    @if($previousPosition)
                        <a href="{{ route('reading.practice.question', ['attempt' => $attempt->id, 'position' => $previousPosition]) }}" class="btn">&larr; Trước</a>
                    @endif

                    @if($nextPosition)
                        <a href="{{ route('reading.practice.question', ['attempt' => $attempt->id, 'position' => $nextPosition]) }}" class="btn">Tiếp &rarr;</a>
                    @endif
                </div>

                <div class="flex items-center space-x-2">
                    @if($attempt->isInProgress())
                        <button type="button" id="finish-btn" class="btn btn-danger">Nộp bài</button>
                    @else
                        <a href="" class="btn">Xem kết quả</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

@if($attempt->metadata['mode'] ?? 'learning' === 'learning')
    @push('scripts')
    <script>
    (function(){
        const form = document.getElementById('answer-form');
        const finishBtn = document.getElementById('finish-btn');

        // Pass PHP metadata into JS safely
        const qMeta = {!! json_encode($question->metadata ?? new stdClass) !!};
        const part = {{ $part ?? ($question->part ?? ($quiz->part ?? 1)) }};

        form.addEventListener('submit', function(e){
            e.preventDefault();
            submitAnswer(false);
        });

        finishBtn && finishBtn.addEventListener('click', function(){
            submitAnswer(true);
        });

        function evaluateClient(){
            // returns 1 if client thinks answer is correct, 0 otherwise
            try{
                if (part === 1) {
                    // collect select values for part1_choice[0..]
                    const selects = Array.from(form.querySelectorAll('select[name^="part1_choice"]'));
                    const selected = selects.map(s => s.value);
                    const correct = qMeta['correct_answers'] || qMeta['answers'] || [];
                    if (!Array.isArray(correct) || correct.length === 0) return 0;
                    if (selected.length < correct.length) return 0;
                    for (let i=0;i<correct.length;i++){
                        if ((selected[i] ?? '') === '') return 0;
                        if (String(selected[i]) !== String(correct[i])) return 0;
                    }
                    return 1;
                }

                if (part === 2) {
                    const txt = (form.querySelector('input[name="part2_order_text"]') || {}).value || '';
                    const arr = txt.trim() === '' ? [] : txt.split(',').map(s => s.trim());
                    const correct = qMeta['correct_order'] || qMeta['answers'] || [];
                    if (!Array.isArray(correct) || arr.length !== correct.length) return 0;
                    for (let i=0;i<correct.length;i++){
                        if (String(arr[i]) !== String(correct[i])) return 0;
                    }
                    return 1;
                }

                if (part === 3) {
                    const items = qMeta['items'] || [];
                    const correctMap = qMeta['answers'] || qMeta['correct'] || {};
                    if (!Array.isArray(items) || items.length === 0) return 0;
                    for (let i=0;i<items.length;i++){
                        const r = form.querySelector('input[name="part3_answer['+i+']"]:checked');
                        const val = r ? r.value : '';
                        const expected = (correctMap && (correctMap[i] !== undefined)) ? String(correctMap[i]) : '';
                        if (val === '' || String(val) !== String(expected)) return 0;
                    }
                    return 1;
                }

                if (part === 4) {
                    const correct = qMeta['correct'] || qMeta['correct_answers'] || qMeta['answers'] || [];
                    if (!Array.isArray(correct) || correct.length === 0) return 0;
                    for (let i=0;i<correct.length;i++){
                        const r = form.querySelector('input[name="part4_choice['+i+']"]:checked');
                        const val = r ? r.value : '';
                        if (val === '' || String(val) !== String(correct[i])) return 0;
                    }
                    return 1;
                }
            } catch(e){
                console.error('evaluateClient error', e);
            }
            return 0;
        }

        function appendClientFlags(fd, isCorrect){
            fd.append('client_provided', '1');
            fd.append('client_is_correct', isCorrect ? '1' : '0');
        }

        function showFeedback(isCorrect){
            // clear previous highlights
            const labels = form.querySelectorAll('label');
            labels.forEach(l => l.classList.remove('bg-green-50','border','border-green-200','bg-red-50','border','border-red-200'));
            if (part === 1) {
                const selects = Array.from(form.querySelectorAll('select[name^="part1_choice"]'));
                selects.forEach(s => {
                    if (!s.value) return;
                    if (isCorrect) s.classList.add('bg-green-50','border','border-green-200'); else s.classList.add('bg-red-50','border','border-red-200');
                });
            } else if (part === 2) {
                const input = form.querySelector('input[name="part2_order_text"]');
                if (input) {
                    if (isCorrect) input.classList.add('bg-green-50','border','border-green-200'); else input.classList.add('bg-red-50','border','border-red-200');
                }
            } else {
                const checked = form.querySelectorAll('input:checked');
                checked.forEach(ch => {
                    const lab = ch.closest('label');
                    if (lab) {
                        if (isCorrect) lab.classList.add('bg-green-50','border','border-green-200'); else lab.classList.add('bg-red-50','border','border-red-200');
                    }
                });
            }
        }

        function submitAnswer(finish){
            const data = new FormData(form);
            if (finish) data.append('action','finish');

            // Evaluate client-side and append flags
            const clientCorrect = evaluateClient();
            appendClientFlags(data, clientCorrect === 1);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                },
                body: data
            }).then(r => r.json()).then(resp => {
                if (resp.success) {
                    const displayCorrect = (resp.hasOwnProperty('is_correct')) ? (resp.is_correct ? 1 : 0) : clientCorrect;
                    showFeedback(displayCorrect === 1);
                    if (finish && resp.redirect) window.location.href = resp.redirect;
                } else {
                    alert(resp.message || 'Có lỗi khi lưu đáp án');
                }
            }).catch(err => {
                console.error(err);
                alert('Lỗi mạng, thử lại');
            });
        }
    })();
    </script>
    @endpush
@endif

@endsection
