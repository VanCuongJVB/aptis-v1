@extends('layouts.app')

@section('title', 'Questions Management')

@section('content')
<div class="container mx-auto px-4 py-8">

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Questions — Quản lý</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.questions.part1.create') }}" class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">Tạo câu hỏi Part 1</a>
            <a href="{{ route('admin.questions.part2.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Tạo câu hỏi Part 2</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded shadow p-4">
        {{-- FILTER FORM --}}
        <form method="get" class="mb-4 flex flex-wrap gap-2 items-end" id="filterForm">
            <div>
                <label class="block text-xs font-semibold mb-1">Quiz</label>
                <select name="quiz_id" id="filter_quiz_id" class="border rounded p-1 min-w-[120px]">
                    <option value="">-- All --</option>
                    @foreach($quizzes as $quiz)
                        <option value="{{ $quiz->id }}" @if(request('quiz_id') == $quiz->id) selected @endif>
                            {{ $quiz->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Set</label>
                <select name="reading_set_id" id="filter_set_id" class="border rounded p-1 min-w-[120px]">
                    <option value="">-- All --</option>
                    @foreach($sets as $set)
                        <option value="{{ $set->id }}" data-quiz="{{ $set->quiz_id }}" @if(request('reading_set_id') == $set->id) selected @endif>
                            {{ $set->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Part</label>
                <select name="part" class="border rounded p-1 min-w-[80px]">
                    <option value="">-- All --</option>
                    @foreach($parts as $part)
                        <option value="{{ $part }}" @if(request('part') == $part) selected @endif>
                            Part {{ $part }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Skill</label>
                @php
                    $skillLabels = ['reading' => 'Đọc hiểu', 'listening' => 'Nghe hiểu'];
                @endphp
                <select name="skill" class="border rounded p-1 min-w-[100px]">
                    <option value="">-- All --</option>
                    @foreach($skills as $skill)
                        <option value="{{ $skill }}" @if(request('skill') == $skill) selected @endif>
                            {{ $skillLabels[$skill] ?? ucfirst($skill) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold mb-1">Tìm kiếm</label>
                <input type="text" name="q" value="{{ request('q') }}" class="border rounded p-1 min-w-[180px]" placeholder="Nội dung...">
            </div>

            <div>
                <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Lọc</button>
                <a href="{{ route('admin.quizzes.questions') }}" class="ml-2 text-gray-500 underline">Reset</a>
            </div>
        </form>

        {{-- JS lọc Set theo Quiz --}}
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const quizSelect = document.getElementById('filter_quiz_id');
            const setSelect  = document.getElementById('filter_set_id');

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
        </script>

        {{-- TABLE --}}
        <div class="overflow-x-auto">
            @php
                $typeLabels = [
                    'reading_gap_filling' => 'Điền từ vào chỗ trống',
                    'single_choice'       => 'Trắc nghiệm 1 đáp án',
                    'multiple_choice'     => 'Trắc nghiệm nhiều đáp án',
                    // thêm nếu có
                ];
            @endphp

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stem</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skill</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quiz / Set</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($questions as $q)
                    <tr class="even:bg-gray-50">
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $questions->firstItem() + $loop->index }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ Str::limit($q->stem ?? $q->title ?? '-', 140) }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $typeLabels[$q->type] ?? ($q->type ?? '-') }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $skillLabels[$q->skill] ?? ($q->skill ? ucfirst($q->skill) : '-') }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ optional($q->quiz)->title ?? '-' }}
                            @if(optional($q->readingSet)->title)
                                / {{ $q->readingSet->title }}
                            @endif
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">

                            @if($q->part == 1)
                                <a href="{{ route('admin.questions.part1.edit', $q) }}"
                                   class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                    Edit
                                </a>
                            @elseif($q->part == 2)
                                <a href="{{ route('admin.questions.part2.edit', $q) }}"
                                   class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                    Edit
                                </a>
                            @else
                                <span class="text-gray-400">Edit</span>
                            @endif

                            <form action="{{ route('admin.questions.part1.destroy', $q) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button
                                    class="inline-flex items-center px-3 py-1 ml-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100"
                                    onclick="return confirm('Delete this question?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                            Không có dữ liệu.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-4 w-100">
            <div class="flex justify-between">
                <style> nav{ width:100% !important; } </style>
                {{ $questions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
