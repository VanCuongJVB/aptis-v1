@extends('layouts.app')
@section('title', 'Sửa câu hỏi')

@section('content')
  <div class="bg-white p-6 rounded shadow max-w-3xl">
    <h1 class="text-xl font-bold mb-4">
      Sửa câu hỏi — {{ strtoupper($quiz->skill) }} — Part {{ $part }} ({{ $quiz->partLabel($part) }}) — Type: {{ $type }}
    </h1>

    <form method="POST" action="{{ route('admin.questions.update', $question) }}" class="space-y-4">
      @csrf @method('PUT')

      <label class="block">
        <span class="text-sm">Part</span>
        <input type="number" min="1" max="4" name="part" class="w-full border rounded px-3 py-2"
          value="{{ old('part', $question->part) }}">
      </label>

      <label class="block">
        <span class="text-sm">Type</span>
        <input type="text" name="type" class="w-full border rounded px-3 py-2" value="{{ old('type', $question->type) }}">
        <small class="text-slate-500">Hệ thống sẽ validate type hợp lệ cho Part.</small>
      </label>

      <label class="block">
        <span class="text-sm">Thứ tự (order)</span>
        <input type="number" name="order" class="w-full border rounded px-3 py-2" min="1"
          value="{{ old('order', $question->order) }}">
      </label>

      <label class="block">
        <span class="text-sm">Stem / Mô tả ngắn</span>
        <textarea name="stem" rows="2"
          class="w-full border rounded px-3 py-2">{{ old('stem', $question->stem) }}</textarea>
      </label>

      {{-- ...phần trên giữ nguyên --}}

      {{-- Tabs theo type --}}
      <div x-data="{ tab: '{{ $type }}' }" class="mt-4">
        <div class="flex gap-2 border-b">
          @foreach(['mcq_single' => 'MCQ', 'dropdown' => 'Dropdown', 'ordering' => 'Ordering', 'matching' => 'Matching', 'heading_matching' => 'Heading Matching'] as $tVal => $tLabel)
            <button type="button" @click="tab='{{ $tVal }}'" class="px-3 py-2 text-sm border-b-2"
              :class="tab==='{{ $tVal }}' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500'">
              {{ $tLabel }}
            </button>
          @endforeach
        </div>

        <div class="grid md:grid-cols-1 gap-4">
          <div>
            {{-- Khối nhập liệu theo tab --}}

            <div x-show="tab==='dropdown'">
              @include('admin.questions.partials.form_mcq', ['old' => old(), 'question' => $question])
            </div>

            <div x-show="tab==='ordering'">
              @include('admin.questions.partials.form_ordering', ['old' => old(), 'question' => $question])
            </div>

            <div x-show="tab==='matching'">
              @include('admin.questions.partials.form_matching', ['old' => old(), 'question' => $question])
            </div>

            <div x-show="tab==='heading_matching'">
              @include('admin.questions.partials.form_headings', ['old' => old(), 'question' => $question])
            </div>
          </div>

          {{-- Preview sống (đặc biệt hữu ích cho matching) --}}
            {{-- <div class="md:sticky md:top-4 h-fit border rounded p-3 bg-slate-50">
              <div class="font-semibold mb-2">Preview</div>
              <div id="livePreview" class="prose max-w-none text-sm"></div>
            </div> --}}
        </div>
      </div>

      {{-- action bar dính dưới --}}
      <div class="sticky bottom-0 bg-white border-t mt-6 py-3">
        <button class="px-4 py-2 rounded bg-blue-600 text-white">Lưu</button>
      </div>

      {{-- ...phần dưới giữ nguyên --}}

{{-- 
      <label class="block">
        <span class="text-sm">Giải thích</span>
        <textarea name="explanation" rows="3"
          class="w-full border rounded px-3 py-2">{{ old('explanation', $question->explanation) }}</textarea>
      </label>

      <button class="px-4 py-2 rounded bg-blue-600 text-white">Lưu</button> --}}
    </form>
  </div>
@endsection