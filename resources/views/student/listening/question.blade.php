@extends('layouts.app')

@section('title', 'Câu hỏi Listening')

@section('content')
<div class="container mx-auto py-6" data-next-url="@if(isset($nextPosition) && $nextPosition){{ route('listening.practice.question', ['attempt' => $attempt->id, 'position' => $nextPosition]) }}@endif" data-final-url="{{ route('listening.practice.result', $attempt) }}">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-lg font-semibold">{{ $quiz->title }} — Câu {{ $position }} / {{ $total }}</h2>
                <p class="text-sm text-gray-500">Part {{ $quiz->part }}</p>
            </div>
            <div class="text-sm text-gray-600"><span>{{ $attempt->started_at ? $attempt->started_at->format('H:i d/m/Y') : '' }}</span></div>
        </div>

        <div class="mb-4">
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

            {{-- @if($audio_text)
                <div class="prose text-sm text-gray-700 mb-3">{!! nl2br(e($audio_text)) !!}</div>
            @endif --}}
        </div>

    <form id="answer-form" method="POST" action="{{ route('listening.practice.answer', ['attempt' => $attempt->id, 'question' => $question->id]) }}" data-qid="{{ $question->id }}">
            @csrf

            <div class="space-y-3 mb-4">
                @if(isset($allQuestions) && $allQuestions->isNotEmpty())
                    @foreach($allQuestions as $q)
                        @php $ansForQ = $answersMap->get($q->id) ?? null; @endphp
                        <div class="mb-6 question-block" data-qid="{{ $q->id }}" data-metadata="{{ json_encode($q->metadata ?? []) }}">
                            <div class="prose mb-3">{!! $q->content ?? $q->title !!}</div>
                            @php $part = $q->part ?? $q->metadata['part'] ?? $quiz->part; @endphp
                            @includeWhen(true, 'student.listening.parts.part' . $part, [
                                'question' => $q,
                                'answer' => $ansForQ
                            ])
                        </div>
                    @endforeach
                @else
                    @php $part = $question->part ?? $question->metadata['part'] ?? $quiz->part; @endphp
                    <div class="question-block" data-qid="{{ $question->id }}" data-metadata="{{ json_encode($question->metadata ?? []) }}">
                        @includeWhen(true, 'student.listening.parts.part' . $part, [
                            'question' => $question,
                            'answer' => $answer ?? null
                        ])
                    </div>
                @endif
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
(function(){
    window.attemptAnswers = window.attemptAnswers || {};
    try {
        const saved = localStorage.getItem('attempt_answers_{{ $attempt->id }}');
        if (saved) {
            const parsed = JSON.parse(saved);
            if (parsed && typeof parsed === 'object') window.attemptAnswers = Object.assign({}, parsed, window.attemptAnswers);
        }
    } catch (e) { /* ignore */ }

    try { 
        window.currentQuestionMeta = Object.assign({}, {!! json_encode($question->metadata ?? []) !!}, { 
            part: {{ $quiz->part ?? 'null' }}, 
            skill: 'listening',
            __debug_info: 'From listening/question.blade.php'
        }); 
    } catch(e){ console.error('Error setting currentQuestionMeta:', e); }
    
    try { window.currentAttemptId = '{{ $attempt->id }}'; } catch(e){}
})();
</script>
@endpush
 
@endsection

@includeWhen(true, 'student.parts.question-footer')
