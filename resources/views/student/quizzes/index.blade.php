@extends('layouts.app')
@section('title','Quizzes')
@section('content')
  <h1 class="text-xl font-bold mb-4">Danh sách bài thi</h1>
  <div class="grid md:grid-cols-2 gap-4">
    @forelse($quizzes as $quiz)
      <div class="bg-white p-4 rounded shadow">
        <div class="flex items-center justify-between">
          <h2 class="font-semibold">{{ $quiz->title }}</h2>
          <span class="text-xs px-2 py-1 rounded bg-slate-100">{{ ucfirst($quiz->skill) }}</span>
        </div>
        <p class="text-sm text-slate-600 mt-2">{{ $quiz->description }}</p>
        <div class="mt-3 flex items-center justify-between">
          <div class="text-sm text-slate-500">Thời lượng: {{ $quiz->duration_minutes }} phút</div>
          <a class="px-3 py-1 rounded bg-red-600 text-white" href="{{ route('student.quizzes.show', $quiz) }}">Vào thi</a>
        </div>
      </div>
    @empty
      <p>Chưa có bài nào.</p>
    @endforelse
  </div>
@endsection
