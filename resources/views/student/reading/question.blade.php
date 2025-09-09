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
                        <a href="{{ route('reading.practice.question', ['attempt' => $attempt->id, 'position' => $previousPosition]) }}" class="btn">&larr; Trước</a>
                    @endif

                    @if(!$allQuestions && $nextPosition)
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

        finishBtn && finishBtn.addEventListener('click', function(){
            submitBulkFinish();
        });

        // collect answers for each .question-block and build a payload keyed by question id
        function collectAllAnswers() {
            const blocks = Array.from(document.querySelectorAll('.question-block'));
            const answers = {};
            blocks.forEach(block => {
                const qid = block.getAttribute('data-qid');
                if (!qid) return;
                const data = {};

                // collect selects
                const selects = Array.from(block.querySelectorAll('select'));
                selects.forEach(s => {
                    const name = s.name || 'select';
                    if (!data['selected']) data['selected'] = {};
                    // append by name to retain structure
                    data['selected'][name] = s.value;
                });

                // collect checked radios/checkboxes
                const inputs = Array.from(block.querySelectorAll('input'));
                inputs.forEach(inp => {
                    if (inp.type === 'radio' && !inp.checked) return;
                    if (inp.type === 'checkbox') {
                        if (!data['selected']) data['selected'] = {};
                        if (!data['selected'][inp.name]) data['selected'][inp.name] = [];
                        if (inp.checked) data['selected'][inp.name].push(inp.value);
                        return;
                    }
                    if (inp.type === 'text' || inp.type === 'hidden') {
                        if (!data['selected']) data['selected'] = {};
                        data['selected'][inp.name] = inp.value;
                    }
                });

                answers[qid] = { metadata: data, selected_option_id: null };
            });
            return answers;
        }

        function computeTotals(answers) {
            // Best-effort: server fallback available. For now compute counts where possible (client-side full-check can be added later)
            const totals = { total_questions: Object.keys(answers).length, correct_answers: null, score_percentage: null };
            return totals;
        }

        function submitBulkFinish(){
            const answers = collectAllAnswers();
            const totals = computeTotals(answers);

            const fd = new FormData();
            fd.append('action','finish');
            fd.append('client_provided','1');
            fd.append('client_totals', JSON.stringify(totals));
            fd.append('answers', JSON.stringify(answers));

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                },
                body: fd
            }).then(r => r.json()).then(resp => {
                if (resp.success) {
                    if (resp.redirect) window.location.href = resp.redirect;
                } else {
                    alert(resp.message || 'Có lỗi khi nộp bài');
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
