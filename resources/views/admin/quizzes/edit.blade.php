@extends('layouts.app')
@section('title','Sửa quiz')
@section('content')
  <div class="grid md:grid-cols-2 gap-4">
    <div class="bg-white p-6 rounded shadow">
      <h1 class="text-xl font-bold mb-4">Quiz</h1>
      <form method="POST" action="{{ route('admin.quizzes.update', $quiz) }}" class="space-y-3">
        @csrf @method('PUT')
        @include('admin.quizzes.partials.form', ['quiz' => $quiz])
        <button class="px-4 py-2 rounded bg-red-600 text-white">Lưu</button>
      </form>
    </div>
    <div class="bg-white p-6 rounded shadow">
      <div class="flex justify-between items-center mb-3">
        <h2 class="font-semibold">Câu hỏi</h2>
        <a class="px-2 py-1 rounded bg-green-600 text-white" href="{{ route('admin.questions.create', $quiz) }}">+ Thêm câu</a>
      </div>
      <div class="space-y-2">
        @foreach($quiz->questions as $q)
          <div class="border rounded p-3">
            <div class="flex justify-between items-center">
              <div class="font-medium">#{{ $q->order }} • {{ $q->type }}</div>
              <a class="text-red-600" href="{{ route('admin.questions.edit', $q) }}">Sửa</a>
            </div>
            <div class="mt-2">{{ $q->stem }}</div>
            <ul class="mt-2 text-sm list-disc ml-6">
              @foreach($q->options as $opt)
                <li>
                  {{ $opt->label }}
                  @if($opt->is_correct) <span class="px-2 py-0.5 rounded bg-green-100 border border-green-300">đúng</span> @endif
                </li>
              @endforeach
            </ul>
          </div>
        @endforeach
      </div>
    </div>
  </div>
@endsection
