@extends('layouts.app')
@section('title','Sửa câu hỏi')
@section('content')
  <div class="bg-white p-6 rounded shadow max-w-2xl">
    <h1 class="text-xl font-bold mb-4">Sửa câu hỏi</h1>
    <form method="POST" action="{{ route('admin.questions.update', $question) }}" class="space-y-3">
      @csrf @method('PUT')
      @include('admin.questions.partials.form', ['question' => $question])
      <button class="px-4 py-2 rounded bg-blue-600 text-white">Lưu</button>
    </form>
  </div>
@endsection
