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
      @foreach($quiz->questions as $q)
        <div class="border rounded p-3">
          <div class="flex justify-between">
            <div class="font-medium">Câu {{ $loop->iteration }}</div>
            <div class="text-xs text-slate-500">Loại: {{ $q->type }}</div>
          </div>
          <div class="mt-2">{{ $q->stem }}</div>

          @if($q->audio_path)
            <div class="mt-3">
              <audio
                class="quiz-audio"
                data-plays-allowed="{{ $quiz->listens_allowed }}"
                data-allow-seek="{{ $quiz->allow_seek ? '1' : '0' }}"
                controls
                src="{{ asset($q->audio_path) }}">
              </audio>
            </div>
          @endif

          <div class="mt-3 space-y-2">
            @foreach($q->options as $opt)
              <label class="flex items-start gap-2">
                @if($q->type === 'single')
                  <input type="radio" name="answers[{{ $q->id }}][]" value="{{ $opt->id }}">
                @else
                  <input type="checkbox" name="answers[{{ $q->id }}][]" value="{{ $opt->id }}">
                @endif
                <span>{{ $opt->label }}</span>
              </label>
            @endforeach
          </div>
        </div>
      @endforeach

      <button class="px-4 py-2 rounded bg-green-600 text-white">Nộp bài</button>
    </form>
  </div>

  <script>
    (function() {
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
