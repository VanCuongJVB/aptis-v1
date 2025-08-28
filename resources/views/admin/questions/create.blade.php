@extends('layouts.app')
@section('title', 'Thêm câu hỏi')

@section('content')
  <div class="bg-white p-6 rounded shadow max-w-3xl">
    <h1 class="text-xl font-bold mb-4">
      Thêm câu hỏi — {{ strtoupper($quiz->skill) }} — Part {{ $part }} ({{ $quiz->partLabel($part) }}) — Type: {{ $type }}
    </h1>

    <form method="POST" action="{{ route('admin.questions.store', $quiz) }}" class="space-y-4">
      @csrf

      <input type="hidden" name="part" value="{{ $part }}">
      <input type="hidden" name="type" value="{{ $type }}">

      <label class="block">
        <span class="text-sm">Thứ tự (order)</span>
        <input type="number" name="order" class="w-full border rounded px-3 py-2" min="1" value="{{ old('order') }}">
      </label>

      {{-- stem dùng cho MCQ / hiển thị mô tả ở loại khác --}}
      <label class="block">
        <span class="text-sm">Stem / Mô tả ngắn</span>
        <textarea name="stem" rows="2" class="w-full border rounded px-3 py-2">{{ old('stem') }}</textarea>
      </label>

      @if(in_array($type, ['mcq_single']))
        <label class="block">
          <span class="text-sm">Audio URL (Listening, nếu có)</span>
          <input type="url" name="audio_url" class="w-full border rounded px-3 py-2" value="{{ old('audio_url') }}">
        </label>
      @endif

      @if(in_array($type, ['dropdown', 'mcq_single']))
        @include('admin.questions.partials.form_mcq', ['old' => old()])
      @elseif($type === 'ordering')
        @include('admin.questions.partials.form_ordering', ['old' => old()])
      @elseif($type === 'matching')
        @include('admin.questions.partials.form_matching', ['old' => old()])
      @elseif($type === 'heading_matching')
        @include('admin.questions.partials.form_headings', ['old' => old()])
      @endif

      <label class="block">
        <span class="text-sm">Giải thích (tuỳ chọn)</span>
        <textarea name="explanation" rows="3" class="w-full border rounded px-3 py-2">{{ old('explanation') }}</textarea>
      </label>

      <button class="px-4 py-2 rounded bg-blue-600 text-white">Lưu</button>
    </form>
  </div>
@endsection