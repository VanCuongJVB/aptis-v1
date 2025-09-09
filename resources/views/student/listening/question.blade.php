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

            <div class="flex items-center justify-between mt-6">
                <div class="space-x-2">
                    @if($previousPosition)
                        <a href="{{ route('listening.practice.question', ['attempt' => $attempt->id, 'position' => $previousPosition]) }}" class="btn">&larr; Trước</a>
                    @endif

                    @if($nextPosition)
                        <a href="{{ route('listening.practice.question', ['attempt' => $attempt->id, 'position' => $nextPosition]) }}" class="btn">Tiếp &rarr;</a>
                    @endif
                </div>

                <div>
                    <button type="button" id="submit-btn" class="btn btn-primary">Nộp</button>
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

    submitBtn && submitBtn.addEventListener('click', function(){
        submitBtn.disabled = true;
        const meta = collectMeta();
        const body = new FormData();
        body.append('action','submit');
        body.append('metadata', JSON.stringify(meta));

        fetch(form.action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value }, body })
        .then(r => r.json())
        .then(resp => {
            submitBtn.disabled = false;
            if (resp.success) {
                // show inline feedback
                if (resp.is_correct) {
                    alert('Đúng');
                } else {
                    alert('Sai. Đáp án: ' + JSON.stringify(resp.correct));
                }

                if (resp.redirect) {
                    window.location.href = resp.redirect;
                }
            } else {
                alert(resp.message || 'Lỗi khi nộp câu trả lời');
            }
        }).catch(err => { console.error(err); submitBtn.disabled = false; alert('Lỗi mạng'); });
    });

})();
</script>
@endpush

@endsection
