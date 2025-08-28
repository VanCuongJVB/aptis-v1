@extends('layouts.app')
@section('title', $quiz->title)

@section('content')
  <div class="bg-white p-4 rounded shadow">
    <h1 class="text-xl font-bold">{{ $quiz->title }}</h1>
    <p class="text-sm text-slate-600 mb-3">{{ $quiz->description }}</p>

    @if($quiz->isListening())
      <div class="mb-4 text-sm">
        <strong>Listening:</strong>
        @if(!$quiz->allow_seek) Không cho tua. @endif
        Số lần nghe: {{ $quiz->listens_allowed }}
      </div>
    @endif

    <form method="POST" action="{{ route('student.quizzes.submit', $quiz) }}" id="quizForm" class="space-y-6">
      @csrf

      {{-- ... header, form mở … --}}
@foreach($quiz->questions as $q)
  <div class="border rounded p-3">
    <div class="flex justify-between">
      <div class="font-medium">Câu {{ $loop->iteration }}</div>
      <div class="text-xs text-slate-500">Loại: {{ $q->type }}</div>
    </div>

    <div class="mt-2 whitespace-pre-wrap">{{ $q->stem }}</div>

    @if($q->type === 'matching')
      @php
        $meta     = $q->meta ?? [];
        $sources  = $meta['sources'] ?? []; // ["titleA", "bodyA", "titleB","bodyB",...]
        $items    = $meta['items'] ?? [];
        $pairCnt  = intdiv(count($sources), 2);
        $labels   = [];
        for ($i=0;$i<max(1,$pairCnt);$i++) { $labels[] = chr(65+$i); } // A,B,C,...
      @endphp

      {{-- Nguồn Person A… --}}
      <div class="grid md:grid-cols-2 gap-3 mt-3">
        @for($i=0; $i<count($sources); $i+=2)
          @php
            $who   = $sources[$i] ?? '';
            $text  = $sources[$i+1] ?? '';
            $label = $labels[intdiv($i,2)] ?? '?';
          @endphp
          <div class="border rounded p-3">
            <div class="font-semibold mb-1">Person {{ $label }}</div>
            @if($who)
              <div class="text-xs text-slate-500 mb-1">{{ $who }}</div>
            @endif
            <div class="text-sm whitespace-pre-wrap">{{ $text }}</div>
          </div>
        @endfor
      </div>

      {{-- Items: chọn A/B/C/D --}}
      <div class="mt-4 space-y-3">
        @foreach($items as $idx => $prompt)
          <div class="flex items-start gap-3">
            <div class="w-8 shrink-0 font-medium">{{ $idx + 1 }}.</div>
            <div class="grow">
              <div class="mb-2">{{ $prompt }}</div>
              <select
                name="answers[{{ $q->id }}][{{ $idx+1 }}]"
                class="border rounded px-2 py-1"
                required
              >
                <option value="">— chọn —</option>
                @foreach($labels as $L)
                  @php $oldVal = old("answers.{$q->id}." . ($idx+1)); @endphp
                  <option value="{{ $L }}" {{ ($oldVal === $L) ? 'selected' : '' }}>
                    {{ $L }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
        @endforeach
      </div>

    @endif
  </div>
@endforeach
{{-- ... nút Nộp bài, đóng form … --}}


      <button class="px-4 py-2 rounded bg-green-600 text-white">Nộp bài</button>
    </form>
  </div>

  <script>
    (function () {
      const audios = document.querySelectorAll('audio.quiz-audio');
      audios.forEach(a => {
        let playsAllowed = parseInt(a.dataset.playsAllowed || '1', 10);
        let playedCount = 0;
        let lastTime = 0;
        let allowSeek = a.dataset.allowSeek === '1';

        a.addEventListener('play', () => {
          if (playedCount >= playsAllowed) {
            a.pause();
            alert('Bạn đã nghe đủ số lần cho phép.');
          }
        });
        a.addEventListener('ended', () => {
          playedCount++;
        });

        if (!allowSeek) {
          a.addEventListener('timeupdate', () => {
            if (a.currentTime > lastTime) {
              lastTime = a.currentTime;
            } else if (a.currentTime < lastTime - 0.35) {
              a.currentTime = lastTime;
            }
          });
          a.addEventListener('seeking', () => {
            if (a.currentTime > lastTime + 0.35) {
              a.currentTime = lastTime;
            }
          });
        }
      });
    })();
  </script>
@endsection