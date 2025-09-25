@extends('layouts.app')

@section('title', 'Questions Management')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Questions — Quản lý</h1>
            <button id="create-question-btn" type="button" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-white shadow hover:bg-emerald-700">Tạo câu hỏi</button>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-2xl bg-white shadow-sm border border-slate-200 p-4">
            {{-- FILTER FORM --}}
            <form method="get" class="mb-6 rounded-2xl border border-slate-200 bg-white/70 p-4 shadow-sm backdrop-blur flex flex-wrap gap-4 items-end" id="filterForm">
                <div class="min-w-[180px] space-y-1">
                    <label class="block text-xs font-semibold tracking-wide text-slate-600">Quiz</label>
                    <select name="quiz_id" id="filter_quiz_id" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 min-w-[120px]">
                        <option value="">-- All --</option>
                        @foreach($quizzes as $quiz)
                            <option value="{{ $quiz->id }}" @if(request('quiz_id') == $quiz->id) selected @endif>
                                {{ $quiz->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[180px] space-y-1">
                    <label class="block text-xs font-semibold tracking-wide text-slate-600">Set</label>
                    <select name="reading_set_id" id="filter_set_id" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 min-w-[120px]">
                        <option value="">-- All --</option>
                        @foreach($sets as $set)
                            <option value="{{ $set->id }}" data-quiz="{{ $set->quiz_id }}"
                                data-part="{{ $set->quiz->part ?? '' }}" data-skill="{{ $set->quiz->skill ?? '' }}"
                                @if(request('reading_set_id') == $set->id) selected @endif>
                                {{ $set->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[180px] space-y-1">
                    <label class="block text-xs font-semibold tracking-wide text-slate-600">Tìm kiếm</label>
                    <input type="text" name="q" value="{{ request('q') }}" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40" placeholder="Nội dung...">
                </div>
                <div>
                    <button type="submit" class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500/60">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 -ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6h18M4 12h16M6 18h12" />
                        </svg>
                        <span>Lọc</span>
                    </button>
                    <a href="{{ route('admin.quizzes.questions') }}"
                        class="inline-flex items-center gap-1 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white shadow hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/60 ml-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m-5 0a8.001 8.001 0 0015.356 2m0 0V15m0-4h-5" />
                        </svg>
                        Reset
                    </a>
                </div>
            </form>

            {{-- JS lọc Set theo Quiz --}}
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const quizSelect = document.getElementById('filter_quiz_id');
                    const setSelect = document.getElementById('filter_set_id');

                    function filterSets() {
                        const quizId = quizSelect.value;
                        Array.from(setSelect.options).forEach(function (opt) {
                            if (!opt.value) { opt.style.display = ''; return; }
                            if (!quizId) {
                                opt.style.display = '';
                            } else if (opt.getAttribute('data-quiz') === quizId) {
                                opt.style.display = '';
                            } else {
                                opt.style.display = 'none';
                            }
                        });
                        // Nếu set đang chọn không thuộc quiz thì reset
                        if (setSelect.selectedOptions.length && setSelect.selectedOptions[0].style.display === 'none') {
                            setSelect.value = '';
                        }
                    }

                    if (quizSelect && setSelect) {
                        quizSelect.addEventListener('change', filterSets);
                        filterSets();
                    }
                });

                document.addEventListener('DOMContentLoaded', function () {
                    const quizSelect = document.getElementById('filter_quiz_id');
                    const setSelect = document.getElementById('filter_set_id');
                    const createBtn = document.getElementById('create-question-btn');

                    function filterSets() {
                        const quizId = quizSelect.value;
                        Array.from(setSelect.options).forEach(function (opt) {
                            if (!opt.value) { opt.style.display = ''; return; }
                            if (!quizId) {
                                opt.style.display = '';
                            } else if (opt.getAttribute('data-quiz') === quizId) {
                                opt.style.display = '';
                            } else {
                                opt.style.display = 'none';
                            }
                        });
                        // Nếu set đang chọn không thuộc quiz thì reset
                        if (setSelect.selectedOptions.length && setSelect.selectedOptions[0].style.display === 'none') {
                            setSelect.value = '';
                        }
                    }

                    if (quizSelect && setSelect) {
                        quizSelect.addEventListener('change', filterSets);
                        filterSets();
                    }

                    // Xử lý nút tạo câu hỏi
                    if (createBtn) {
                        createBtn.addEventListener('click', function () {
                            const setOpt = setSelect.options[setSelect.selectedIndex];
                            if (!setOpt || !setOpt.value) {
                                alert('Vui lòng chọn Set trước khi tạo câu hỏi!');
                                return;
                            }
                            const part = setOpt.getAttribute('data-part');
                            const skill = setOpt.getAttribute('data-skill');
                            let url = '';
                            if (skill === 'reading') {
                                if (part == 1) url = '{{ route('admin.questions.part1.create') }}?reading_set_id=' + setOpt.value;
                                else if (part == 2) url = '{{ route('admin.questions.part2.create') }}?reading_set_id=' + setOpt.value;
                                else if (part == 3) url = '{{ route('admin.questions.part3.create') }}?reading_set_id=' + setOpt.value;
                                else url = '{{ route('admin.questions.part1.create') }}?reading_set_id=' + setOpt.value;
                            } else {
                                // Nếu có listening part sau này thì bổ sung
                                url = '{{ route('admin.questions.part1.create') }}?reading_set_id=' + setOpt.value;
                            }
                            window.location.href = url;
                        });
                    }
                });
            </script>

            {{-- TABLE --}}
            <div class="overflow-x-auto">
                @php
                    $typeLabels = [
                        'reading_gap_filling' => 'Điền từ vào chỗ trống',
                        'single_choice' => 'Trắc nghiệm 1 đáp án',
                        'multiple_choice' => 'Trắc nghiệm nhiều đáp án',
                        // thêm nếu có
                    ];
                @endphp
                <table class="min-w-full border border-slate-200 rounded-2xl divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Stem</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Skill</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Quiz / Set</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        @forelse($questions as $q)
                            <tr class="even:bg-slate-50/60">
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-700">{{ $questions->firstItem() + $loop->index }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">{{ Str::limit($q->stem ?? $q->title ?? '-', 140) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">{{ $skillLabels[$q->skill] ?? ($q->skill ? ucfirst($q->skill) : '-') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">{{ optional($q->quiz)->title ?? '-' }}@if(optional($q->readingSet)->title) / {{ $q->readingSet->title }}@endif</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    @if($q->skill == 'listening' && $q->part == 1)
                                        <a href="{{ route('admin.questions.listening.part1.edit', $q) }}" class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-3 py-1.5 text-indigo-700 hover:bg-indigo-100 border border-indigo-200">Edit</a>
                                        <form action="{{ route('admin.questions.listening.part1.destroy', $q) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-3 py-1.5 text-rose-700 hover:bg-rose-100 border border-rose-200 ml-2" onclick="return confirm('Delete this question?')">Delete</button>
                                        </form>
                                    @elseif($q->part == 1)
                                        <a href="{{ route('admin.questions.part1.edit', $q) }}" class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-3 py-1.5 text-indigo-700 hover:bg-indigo-100 border border-indigo-200">Edit</a>
                                        <form action="{{ route('admin.questions.part1.destroy', $q) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-3 py-1.5 text-rose-700 hover:bg-rose-100 border border-rose-200 ml-2" onclick="return confirm('Delete this question?')">Delete</button>
                                        </form>
                                    @elseif($q->skill == 'listening' && $q->part == 2)
                                        <a href="{{ route('admin.questions.listening.part2.edit', $q) }}" class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-3 py-1.5 text-indigo-700 hover:bg-indigo-100 border border-indigo-200">Edit</a>
                                        <form action="{{ route('admin.questions.listening.part2.destroy', $q) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-3 py-1.5 text-rose-700 hover:bg-rose-100 border border-rose-200 ml-2" onclick="return confirm('Delete this question?')">Delete</button>
                                        </form>
                                    @elseif($q->part == 2)
                                        <a href="{{ route('admin.questions.part2.edit', $q) }}" class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-3 py-1.5 text-indigo-700 hover:bg-indigo-100 border border-indigo-200">Edit</a>
                                        <form action="{{ route('admin.questions.part2.destroy', $q) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-3 py-1.5 text-rose-700 hover:bg-rose-100 border border-rose-200 ml-2" onclick="return confirm('Delete this question?')">Delete</button>
                                        </form>
                                    @elseif($q->skill == 'listening' && $q->part == 3)
                                        <a href="{{ route('admin.questions.listening.part3.edit', $q) }}" class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-3 py-1.5 text-indigo-700 hover:bg-indigo-100 border border-indigo-200">Edit</a>
                                        <form action="{{ route('admin.questions.listening.part3.destroy', $q) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-3 py-1.5 text-rose-700 hover:bg-rose-100 border border-rose-200 ml-2" onclick="return confirm('Delete this question?')">Delete</button>
                                        </form>
                                    @elseif($q->part == 3)
                                        <a href="{{ route('admin.questions.part3.edit', $q) }}" class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-3 py-1.5 text-indigo-700 hover:bg-indigo-100 border border-indigo-200">Edit</a>
                                        <form action="{{ route('admin.questions.part3.destroy', $q) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-3 py-1.5 text-rose-700 hover:bg-rose-100 border border-rose-200 ml-2" onclick="return confirm('Delete this question?')">Delete</button>
                                        </form>
                                    @elseif($q->skill == 'listening' && $q->part == 4)
                                        <a href="{{ route('admin.questions.listening.part4.edit', $q) }}" class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-3 py-1.5 text-indigo-700 hover:bg-indigo-100 border border-indigo-200">Edit</a>
                                        <form action="{{ route('admin.questions.listening.part4.destroy', $q) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-3 py-1.5 text-rose-700 hover:bg-rose-100 border border-rose-200 ml-2" onclick="return confirm('Delete this question?')">Delete</button>
                                        </form>
                                    @elseif($q->part == 4)
                                        <a href="{{ route('admin.questions.part4.edit', $q) }}" class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-3 py-1.5 text-indigo-700 hover:bg-indigo-100 border border-indigo-200">Edit</a>
                                        <form action="{{ route('admin.questions.part4.destroy', $q) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-3 py-1.5 text-rose-700 hover:bg-rose-100 border border-rose-200 ml-2" onclick="return confirm('Delete this question?')">Delete</button>
                                        </form>
                                    @else
                                        <span class="text-gray-400">Edit</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500">Không có dữ liệu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <style>
                    nav {
                        width: 100% !important;
                    }
                </style>
                {{ $questions->links() }}
            </div>
        </div>
    </div>
@endsection