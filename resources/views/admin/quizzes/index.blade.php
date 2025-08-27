@extends('layouts.app')
@section('title','Admin - Quizzes')
@section('content')
  <div class="flex justify-between items-center mb-3">
    <h1 class="text-xl font-bold">Quizzes</h1>
    <a class="px-3 py-1 rounded bg-red-600 text-white" href="{{ route('admin.quizzes.create') }}">+ Tạo quiz</a>
  </div>
  <div class="bg-white rounded shadow divide-y">
    @foreach($quizzes as $quiz)
      <div class="p-3 flex items-center justify-between">
        <div>
          <div class="font-medium">{{ $quiz->title }}</div>
          <div class="text-xs text-slate-500">Skill: {{ $quiz->skill }} • {{ $quiz->questions()->count() }} câu</div>
        </div>
        <div class="space-x-2">
          <a class="px-2 py-1 bg-slate-200 rounded" href="{{ route('admin.quizzes.edit', $quiz) }}">Sửa</a>
          <form class="inline" method="POST" action="{{ route('admin.quizzes.destroy', $quiz) }}" onsubmit="return confirm('Xoá quiz?')">
            @csrf @method('DELETE')
            <button class="px-2 py-1 bg-red-600 text-white rounded">Xoá</button>
          </form>
        </div>
      </div>
    @endforeach
  </div>
  <div class="mt-3">{{ $quizzes->links() }}</div>
@endsection
