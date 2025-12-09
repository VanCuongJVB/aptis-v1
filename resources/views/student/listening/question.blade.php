@extends('layouts.app')

@section('title', 'Câu hỏi Listening')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    // Lấy đường dẫn audio: ưu tiên metadata mới nhất
    $audioPath = data_get($question->metadata, 'audio')
        ?? $question->audio_path
        ?? $question->audio;

    // Convert sang URL phát được
    $audioUrl = null;
    if ($audioPath) {
        if (Str::startsWith($audioPath, ['http://', 'https://'])) {
            $audioUrl = $audioPath;
        } elseif (Str::startsWith($audioPath, ['/'])) {
            $audioUrl = asset(ltrim($audioPath, '/'));
        } else {
            $audioUrl = Storage::url($audioPath);
        }
    }

    $speakers = data_get($question->metadata, 'speakers', []);
    $part = $question->part ?? data_get($question->metadata, 'part') ?? $quiz->part ?? null;
    $isPart2 = ((int)$part === 2) && is_array($speakers) && count($speakers) > 0;
@endphp

<div class="container mx-auto py-6" 
     data-next-url="@if(isset($nextPosition) && $nextPosition){{ route('listening.practice.question', ['attempt' => $attempt->id, 'position' => $nextPosition]) }}@endif" 
     data-final-url="{{ route('listening.practice.result', $attempt) }}">
    
    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow" style="padding-bottom: 180px">
        {{-- Header --}}
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-lg font-semibold">{{ $quiz->title }} — Câu {{ $position }} / {{ $total }}</h2>
                <p class="text-sm text-gray-500">Part {{ $quiz->part }}</p>
            </div>
            <div class="text-sm text-gray-600">
                <span>{{ $attempt->started_at ? $attempt->started_at->format('H:i d/m/Y') : '' }}</span>
            </div>
        </div>

        {{-- Audio / Description --}}
        <div class="mb-4"></div>

        {{-- Answer Form --}}
        <form id="answer-form" method="POST" action="{{ route('listening.practice.answer', ['attempt' => $attempt->id, 'question' => $question->id]) }}" data-qid="{{ $question->id }}">
            @csrf
            <div class="space-y-3 mb-4">
                @php $resolvedPart = $part ?? $quiz->part; @endphp
                <div class="question-block" data-qid="{{ $question->id }}" data-metadata='@json($question->metadata ?? [])'>
                    @includeWhen(true, 'student.listening.parts.part' . $resolvedPart, [
                        'question' => $question,
                        'answer' => $answer ?? null,
                        'audioUrl' => $audioUrl
                    ])
                </div>
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
                if (parsed && typeof parsed === 'object') {
                    window.attemptAnswers = Object.assign({}, parsed, window.attemptAnswers);
                }
            }
        } catch(e){}

        try { 
            window.currentQuestionMeta = Object.assign({}, @json($question->metadata ?? []), { 
                part: {{ $quiz->part ?? 'null' }}, 
                skill: 'listening'
            }); 
        } catch(e){ console.error(e); }

        try { window.currentAttemptId = '{{ $attempt->id }}'; } catch(e){}
    })();

    // Toggle description for Part2 & Part3
    document.addEventListener('click', function(e){
        const btn = e.target.closest('.desc-toggle-link');
        if (!btn) return;

        e.preventDefault();
        const qid = btn.getAttribute('data-qid') || btn.id.replace('desc-toggle-', '');
        const idx = btn.getAttribute('data-idx');
        const boxId = (idx !== null && idx !== undefined) ? ('desc-box-' + qid + '-' + idx) : ('desc-box-' + qid);
        const box = document.getElementById(boxId);
        if(box){
            box.classList.toggle('hidden');
            btn.textContent = box.classList.contains('hidden') ? 'Hiển thị mô tả' : 'Ẩn mô tả';
        }
    });

    // Play all audios sequentially (Part2)
    document.addEventListener('DOMContentLoaded', function(){
        const playAllBtn = document.getElementById('play-all-{{ $question->id }}');
        if(!playAllBtn) return;
        const audios = Array.from(document.querySelectorAll('.playall-audio'));
        if (!audios.length) return;

        let isPlayingAll = false, userStopped = false;

        function stopAll(){
            audios.forEach(a => { a.pause(); a.currentTime=0; a.onended=null; });
            isPlayingAll = false;
            userStopped = false;
            playAllBtn.textContent = 'Phát tất cả';
        }

        function playNext(idx){
            if(!isPlayingAll || idx >= audios.length){ stopAll(); return; }
            const audio = audios[idx];
            audios.forEach((a,i)=>{ if(i!==idx){ a.pause(); a.currentTime=0;} });
            audio.currentTime=0;
            
            // Handle async play on Safari
            const playPromise = audio.play();
            if (playPromise !== undefined) {
                playPromise
                    .catch(error => {
                        console.error('Audio play error:', error);
                        if(isPlayingAll && !userStopped) playNext(idx+1);
                        else stopAll();
                    });
            }
            
            audio.onended=function(){ if(isPlayingAll && !userStopped) playNext(idx+1); else stopAll(); }
        }

        playAllBtn.addEventListener('click', function(){
            if(isPlayingAll){ userStopped=true; stopAll(); }
            else{ isPlayingAll=true; userStopped=false; playAllBtn.textContent='Dừng phát tất cả'; playNext(0); }
        });
    });
</script>
@endpush

@endsection

@includeWhen(true, 'student.parts.question-footer')
