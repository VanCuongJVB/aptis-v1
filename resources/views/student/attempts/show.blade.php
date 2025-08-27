@extends('layouts.app')
@section('title','Kết quả')
@section('content')
  <div class="bg-white p-6 rounded shadow">
    <h1 class="text-xl font-bold mb-3">Kết quả</h1>
    <div class="mb-4">
      <div><strong>Bài:</strong> {{ $attempt->quiz->title }}</div>
      <div><strong>Điểm thô:</strong> {{ $attempt->score_raw }} / {{ $attempt->quiz->questions->count() }}</div>
      <div><strong>Phần trăm:</strong> {{ number_format($attempt->score_percent, 2) }}%</div>
    </div>
    <div class="space-y-3">
      @foreach($attempt->quiz->questions as $q)
        @php
          $item = $itemsByQid[$q->id] ?? null;
          $selected = collect($item?->selected_option_ids ?? []);
          $correctIds = $q->options->where('is_correct', true)->pluck('id');
        @endphp
        <div class="border rounded p-3">
          <div class="flex justify-between items-center">
            <div class="font-medium">Câu {{ $loop->iteration }}</div>
            <div class="text-xs {{ $item && $item->is_correct ? 'text-green-600' : 'text-red-600' }}">
              {{ $item && $item->is_correct ? 'Đúng' : 'Sai' }}
            </div>
          </div>
          <div class="mt-2">{{ $q->stem }}</div>
          <ul class="mt-2 space-y-1">
            @foreach($q->options as $opt)
              <li class="text-sm">
                @if($correctIds->contains($opt->id))
                  <span class="px-2 py-0.5 rounded bg-green-100 border border-green-300">Đáp án đúng</span>
                @endif
                @if($selected->contains($opt->id))
                  <span class="px-2 py-0.5 rounded bg-blue-100 border border-blue-300">Bạn chọn</span>
                @endif
                <span class="ml-2">{{ $opt->label }}</span>
              </li>
            @endforeach
          </ul>
        </div>
      @endforeach
    </div>
  </div>
@endsection
