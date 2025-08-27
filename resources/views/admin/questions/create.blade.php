@extends('layouts.app')
@section('title','Thêm câu hỏi')
@section('content')
  <div class="bg-white p-6 rounded shadow max-w-2xl">
    <h1 class="text-xl font-bold mb-4">Thêm câu hỏi cho: {{ $quiz->title }}</h1>
    <form method="POST" action="{{ route('admin.questions.store', $quiz) }}" class="space-y-3">
      @csrf
      @include('admin.questions.partials.form', ['question' => null])
      <button class="px-4 py-2 rounded bg-blue-600 text-white">Lưu</button>
    </form>
  </div>
@endsection
