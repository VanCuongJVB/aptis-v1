@extends('layouts.app')

@section('title', 'Danh sách câu hỏi của Set: ' . ($set->title ?? ''))

@section('content')
@php
    use Illuminate\Support\Str;

    // Part hiện tại của trang
    $currentPart = (int)($part ?? request('part', 1));

    // Skill của Set (ưu tiên set->skill, rồi quiz->skill, rồi skill của câu hỏi đầu tiên; mặc định 'reading')
    $setSkill = Str::lower(trim($set->skill
        ?? optional($set->quiz)->skill
        ?? optional($set->questions->first())->skill
        ?? 'reading'));

    $isListening = $setSkill === 'listening';

    // Filter chỉ còn q
    $qInput    = request('q');
    $hasFilter = filled($qInput);
@endphp

<div class="container mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold">
                Danh sách câu hỏi của Set: {{ $set->title }}
            </h1>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-sm font-medium
                            {{ $isListening ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700' }}">
                    Skill: {{ ucfirst($setSkill) }}
                </span>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700 border border-slate-200">
                    Đang xem: Part <span class="ml-1">{{ $currentPart }}</span>
                </span>
            </div>
        </div>

        {{-- Nút tạo câu hỏi theo skill + part --}}
        <div>
            @if($isListening)
                <a href="{{ route('admin.questions.listening.part' . $currentPart . '.create', ['reading_set_id' => $set->id]) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-white shadow hover:bg-blue-700
                          focus:outline-none focus:ring-2 focus:ring-blue-500/60">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 -ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tạo Question mới – Part {{ $currentPart }} (Listening)
                </a>
            @else
                <a href="{{ route('admin.questions.part' . $currentPart . '.create', ['reading_set_id' => $set->id]) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-white shadow hover:bg-emerald-700
                          focus:outline-none focus:ring-2 focus:ring-emerald-500/60">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 -ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tạo Question mới – Part {{ $currentPart }} (Reading)
                </a>
            @endif
        </div>
    </div>

    <div class="mb-4 text-gray-600">
        Quiz: <b>{{ optional($set->quiz)->title ?? '-' }}</b>
    </div>

    <div class="rounded-2xl bg-white shadow-sm border border-slate-200 p-4">
        {{-- Filter (chỉ còn q) --}}
                <form id="filterForm" method="get"
                            action="{{ route('admin.sets.questions', ['set' => $set->id]) }}"
                            class="mb-6 rounded-2xl border border-slate-200 bg-white/70 p-4 shadow-sm backdrop-blur">
                        <input type="hidden" name="part" value="{{ $currentPart }}">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 items-end">
                <div class="space-y-1 lg:col-span-3">
                    <label for="q" class="block text-xs font-semibold tracking-wide text-slate-600">Tìm kiếm</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"
                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                            <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z"/>
                        </svg>
                        <input id="q" type="text" name="q" value="{{ $qInput }}" placeholder="Tìm theo stem hoặc tiêu đề…"
                               class="w-full rounded-xl border border-slate-200 bg-white px-10 py-2.5 text-sm text-slate-800 placeholder-slate-400
                                      shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40"/>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button id="btnFilter" type="submit"
                            class="inline-flex min-w-[110px] items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5
                                   text-sm font-semibold text-white shadow hover:bg-blue-700 active:bg-blue-800
                                   focus:outline-none focus:ring-2 focus:ring-blue-500/60">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 -ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6h18M4 12h16M6 18h12"/>
                        </svg>
                        <span>Lọc</span>
                        <svg id="btnFilterSpinner" class="hidden h-5 w-5 animate-spin" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4A4 4 0 004 12z"/>
                        </svg>
                    </button>

                    @if ($hasFilter)
                        <a href="{{ route('admin.sets.questions', ['set' => $set->id, 'part' => $currentPart]) }}"
                           class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5
                                  text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Xoá bộ lọc
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">STT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Stem</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse($questions as $q)
                        <tr class="even:bg-slate-50/60">
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-700">
                                @if(is_object($questions) && method_exists($questions, 'firstItem'))
                                    {{ $questions->firstItem() + $loop->index }}
                                @else
                                    {{ $loop->iteration }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                {{ Str::limit($q->stem ?? $q->title ?? '-', 140) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">{{ $q->type ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                @if($isListening)
                                    {{-- Listening routes --}}
                                    <a href="{{ route('admin.questions.listening.part' . $currentPart . '.edit', $q) }}"
                                       class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-3 py-1.5 text-blue-700 hover:bg-blue-100 border border-blue-200">Edit</a>
                                    <form action="{{ route('admin.questions.listening.part' . $currentPart . '.destroy', $q) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-3 py-1.5 text-rose-700 hover:bg-rose-100 border border-rose-200 ml-2"
                                                onclick="return confirm('Delete this question?')">Delete</button>
                                    </form>
                                @else
                                    {{-- Reading routes --}}
                                    <a href="{{ route('admin.questions.part' . $currentPart . '.edit', $q) }}"
                                       class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-3 py-1.5 text-indigo-700 hover:bg-indigo-100 border border-indigo-200">Edit</a>
                                    <form action="{{ route('admin.questions.part' . $currentPart . '.destroy', $q) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-3 py-1.5 text-rose-700 hover:bg-rose-100 border border-rose-200 ml-2"
                                                onclick="return confirm('Delete this question?')">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">Không có câu hỏi nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Pagination --}}
        <div class="mt-4">
            @if(is_object($questions) && method_exists($questions, 'appends'))
                {{ $questions->appends(request()->except('page'))->links() }}
            @endif
        </div>
    </div>

    {{-- Back --}}
    <div class="mt-4">
        <a href="{{ route('admin.quizzes.sets') }}"
           class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-blue-700 hover:bg-blue-50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Quay lại danh sách Sets
        </a>
    </div>
</div>

{{-- Scripts: chỉ giữ spinner cho nút Lọc --}}
<script>
    (function() {
        const filterForm = document.getElementById('filterForm');
        const btn = document.getElementById('btnFilter');
        const spinner = document.getElementById('btnFilterSpinner');
        if (filterForm && btn && spinner) {
            filterForm.addEventListener('submit', function () {
                btn.disabled = true;
                spinner.classList.remove('hidden');
            }, { once: true });
        }
    })();
    </script>
@endsection
