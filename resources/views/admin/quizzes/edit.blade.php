@extends('layouts.app')
@section('title', 'Admin - Quizzes')
@section('content')
  <div class="bg-white p-6 rounded shadow">
    <div class="flex justify-between items-center mb-3">
      <h2 class="font-semibold">Câu hỏi</h2>
    </div>

    <div class="space-y-4">
      @foreach($parts as $partIndex => $P)
        @php
          $qs = $quiz->questions()->where('part', $partIndex)->with('options')->get();
          $types = $P['types'] ?? [];
        @endphp

        <div class="border rounded-xl overflow-hidden">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 px-4 py-3 bg-slate-50">
            <div class="font-semibold">Part {{ $partIndex }} — {{ $P['label'] ?? '' }}</div>
            <div class="flex flex-wrap gap-2">
              @foreach($types as $t)
                <a class="px-3 py-1.5 rounded-lg bg-green-600 hover:bg-green-700 text-white"
                  href="{{ route('admin.questions.create', [$quiz, 'part' => $partIndex, 'type' => $t]) }}">
                  + Thêm ({{ $t }})
                </a>
              @endforeach
            </div>
          </div>

          <div class="divide-y">
            @forelse($qs as $q)
              <div class="p-4">
                <div class="flex items-center justify-between">
                  <div class="font-medium">#{{ $q->order }} • {{ $q->type }}</div>
                  <div class="space-x-2">
                    <a class="text-blue-600" href="{{ route('admin.questions.edit', $q) }}">Sửa</a>
                    <form class="inline" method="POST" action="{{ route('admin.questions.destroy', $q) }}"
                      onsubmit="return confirm('Xoá câu hỏi này?')">
                      @csrf @method('DELETE')
                      <button class="text-rose-600">Xoá</button>
                    </form>
                  </div>
                </div>

                {{-- preview ngắn theo type (giữ nguyên như bạn đang có) --}}
                {{-- @includeIf('admin.questions._preview', ['q' => $q]) --}}
              </div>
            @empty
              <div class="p-4 text-sm text-slate-500">Chưa có câu hỏi.</div>
            @endforelse
          </div>
        </div>
      @endforeach
    </div>
  </div>
@endsection