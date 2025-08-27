@extends('layouts.app')
@section('title','Tạo quiz')
@section('content')
  <div class="bg-white p-6 rounded shadow max-w-2xl">
    <h1 class="text-xl font-bold mb-4">Tạo quiz</h1>
    <form method="POST" action="{{ route('admin.quizzes.store') }}" class="space-y-3">
      @csrf
      @include('admin.quizzes.partials.form', ['quiz' => null])
      <button class="px-4 py-2 rounded bg-red-600 text-white">Lưu</button>
    </form>
  </div>
@endsection
