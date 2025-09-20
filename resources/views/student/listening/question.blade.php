@extends('layouts.app')

@section('title', 'Câu hỏi Listening')

@section('content')
<div class="container mx-auto py-6" 
     data-next-url="@if(isset($nextPosition) && $nextPosition){{ route('listening.practice.question', ['attempt' => $attempt->id, 'position' => $nextPosition]) }}@endif" 
     data-final-url="{{ route('listening.practice.result', $attempt) }}">
    
    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
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
        @php
            $audio = $question->audio ?? ($question->metadata['audio'] ?? null);
            $speakers = $question->metadata['speakers'] ?? null;
            $part = $question->part ?? $question->metadata['part'] ?? $quiz->part ?? null;
            $isPart2 = $part == 2 && is_array($speakers);
        @endphp

        <div class="mb-4">
            @if($isPart2)
                <div class="mb-2">
                    <button type="button" id="play-all-{{ $question->id }}" class="mb-3 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none text-sm">
                        Phát tất cả
                    </button>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($speakers as $spIdx => $sp)
                            <div class="border rounded p-2 flex flex-col">
                                <div class="font-medium text-sm mb-1 flex items-center">
                                    {{ $sp['label'] ?? 'Speaker '.chr(65+$spIdx) }}
                                    @if(!empty($sp['description']))
                                        <a href="#" id="desc-toggle-{{ $question->id }}-{{ $spIdx }}" data-qid="{{ $question->id }}" data-idx="{{ $spIdx }}" class="desc-toggle-link text-blue-600 underline text-xs ml-2">
                                            Hiển thị mô tả
                                        </a>
                                    @endif
                                </div>
                                @if(!empty($sp['audio']))
                                    <audio controls preload="none" class="w-full mb-1 playall-audio" id="audio-{{ $question->id }}-{{ $spIdx }}">
                                        <source src="/{{ ltrim($sp['audio'], '/') }}" type="audio/mpeg">
                                        Trình duyệt của bạn không hỗ trợ phát audio.
                                    </audio>
                                @else
                                    <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                                        <span>🎧</span> Không có file âm thanh cho speaker này.
                                    </div>
                                @endif
                                <div id="desc-box-{{ $question->id }}-{{ $spIdx }}" class="speaker-desc text-gray-600 text-sm mb-2 hidden mt-1 bg-yellow-50 border border-yellow-200 rounded p-2">
                                    {{ $sp['description'] ?? '' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($audio)
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
        </div>

        {{-- Answer Form --}}
        <form id="answer-form" method="POST" action="{{ route('listening.practice.answer', ['attempt' => $attempt->id, 'question' => $question->id]) }}" data-qid="{{ $question->id }}">
            @csrf
            <div class="space-y-3 mb-4">
                @php $part = $question->part ?? $question->metadata['part'] ?? $quiz->part; @endphp
                <div class="question-block" data-qid="{{ $question->id }}" data-metadata="{{ json_encode($question->metadata ?? []) }}">
                    @includeWhen(true, 'student.listening.parts.part' . $part, [
                        'question' => $question,
                        'answer' => $answer ?? null
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
                if (parsed && typeof parsed === 'object') window.attemptAnswers = Object.assign({}, parsed, window.attemptAnswers);
            }
        } catch(e){}

        try { 
            window.currentQuestionMeta = Object.assign({}, {!! json_encode($question->metadata ?? []) !!}, { 
                part: {{ $quiz->part ?? 'null' }}, 
                skill: 'listening'
            }); 
        } catch(e){ console.error(e); }

        try { window.currentAttemptId = '{{ $attempt->id }}'; } catch(e){}
    })();

    // Toggle description for Part2 & Part3
    document.addEventListener('click', function(e){
        var btn = e.target;
        if (!btn.classList.contains('desc-toggle-link')) return;

        e.preventDefault();
        var qid = btn.getAttribute('data-qid') || btn.id.replace('desc-toggle-', '');
        var idx = btn.getAttribute('data-idx');
        var boxId = idx !== null ? 'desc-box-' + qid + '-' + idx : 'desc-box-' + qid;
        var box = document.getElementById(boxId);
        if(box){
            box.classList.toggle('hidden');
            btn.textContent = box.classList.contains('hidden') ? 'Hiển thị mô tả' : 'Ẩn mô tả';
        }
    });

    // Play all audios sequentially (Part2)
    document.addEventListener('DOMContentLoaded', function(){
        var playAllBtn = document.getElementById('play-all-{{ $question->id }}');
        if(!playAllBtn) return;
        var audios = Array.from(document.querySelectorAll('.playall-audio'));
        var isPlayingAll = false, userStopped = false;

        function stopAll(){
            audios.forEach(a => { a.pause(); a.currentTime=0; a.onended=null; });
            isPlayingAll = userStopped = false;
            playAllBtn.textContent = 'Phát tất cả';
        }

        function playNext(idx){
            if(!isPlayingAll || idx >= audios.length){ stopAll(); return; }
            var audio = audios[idx];
            audios.forEach((a,i)=>{ if(i!==idx){ a.pause(); a.currentTime=0;} });
            audio.currentTime=0; audio.play();
            audio.onended=function(){ if(isPlayingAll && !userStopped) playNext(idx+1); else stopAll();}
        }

        playAllBtn.addEventListener('click', function(){
            if(isPlayingAll){ userStopped=true; stopAll();}
            else{ isPlayingAll=true; userStopped=false; playAllBtn.textContent='Dừng phát tất cả'; playNext(0);}
        });
    });
    </script>
    @endpush

@endsection

@includeWhen(true, 'student.parts.question-footer')
