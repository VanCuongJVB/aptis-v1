@extends('layouts.app')

@section('title', 'Câu hỏi Reading')

@section('content')
<div class="container mx-auto py-6">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow" style="margin-bottom: 135px;">
        <!-- Header -->
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-lg font-semibold">{{ $quiz->title }} — Câu {{ $position }} / {{ $total }}</h2>
                <p class="text-sm text-gray-500">Part {{ $quiz->part }}</p>
            </div>
            <div class="text-sm text-gray-600">
                {{ $attempt->started_at ? $attempt->started_at->format('H:i d/m/Y') : '' }}
            </div>
        </div>

        <!-- Question content -->
        <div class="mb-4">
            @if(isset($question))
                <div class="prose">{!! $question->content ?? $question->title !!}</div>
            @endif
        </div>

        @php
            $allQuestions = $allQuestions ?? null;
            $previousPosition = $previousPosition ?? null;
            $nextPosition = $nextPosition ?? null;
            $answer = $answer ?? null;

            if (isset($allQuestions) && $allQuestions->isNotEmpty()) {
                $formAction = route('reading.practice.answer', [
                    'attempt' => $attempt->id,
                    'question' => $allQuestions->first()->id
                ]);
            } else {
                $formAction = route('reading.practice.answer', [
                    'attempt' => $attempt->id,
                    'question' => $question->id
                ]);
            }
        @endphp

        <!-- Form -->
        <form id="answer-form" method="POST" action="{{ $formAction }}">
            @csrf
            <div class="space-y-3 mb-4">
                @if(isset($allQuestions) && $allQuestions->isNotEmpty())
                    @foreach($allQuestions as $q)
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
                    @php $part = $question->part ?? $question->metadata['part'] ?? $quiz->part; @endphp
                    <div class="question-block" data-qid="{{ $question->id }}">
                        @includeWhen(true, 'student.reading.parts.part' . $part, [
                            'question' => $question,
                            'answer' => $answer ?? null
                        ])
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>

<div id="inline-feedback" class="hidden"></div>

@if(($attempt->metadata['mode'] ?? 'learning') === 'learning')
    @push('scripts')
    <script>
    window.currentQuestionMeta = {!! json_encode($question->metadata ?? []) !!};
    (function(){
        const finalBtn = document.getElementById('final-submit-btn');
        if (finalBtn) {
            finalBtn.addEventListener('click', function(){
                if (!confirm('Bạn chắc chắn muốn nộp bài?')) return;
                finalBtn.disabled = true;
                try {
                    localStorage.setItem(storageKey(attemptId), JSON.stringify(window.attemptAnswers || {}));
                } catch (e) {}

                const batchUrl = window.batchSubmitUrl;
                fetch(batchUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                    },
                    body: JSON.stringify({ answers: window.attemptAnswers || {}, final: true })
                })
                .then(r => r.json())
                .then(resp => {
                    finalBtn.disabled = false;
                    if (resp.success) {
                        try { localStorage.removeItem(storageKey(attemptId)); } catch(e){}
                        window.location.href = resp.redirect || '/';
                    } else {
                        alert(resp.message || 'Lỗi khi nộp bài');
                    }
                })
                .catch(() => {
                    finalBtn.disabled = false;
                    alert('Lỗi mạng hoặc server');
                });
            });
        }
    })();
    </script>
    @endpush
@endif

@endsection

@php
    // compute a next url when a next position is available; footer will use it for AJAX navigation
    $nextUrlForFooter = null;
    if (!empty($nextPosition)) {
        // try to build a next-question url; if route differs in your app change accordingly
        try {
            $nextUrlForFooter = route('reading.practice.question', ['attempt' => $attempt->id, 'position' => $nextPosition]);
        } catch (\Throwable $e) {
            $nextUrlForFooter = null;
        }
    }
@endphp

@includeWhen(true, 'student.parts.question-footer', [
    'nextUrl' => $nextUrlForFooter,
    'position' => $position ?? null,
    'total' => $total ?? null,
    'attemptId' => $attempt->id ?? null
])
