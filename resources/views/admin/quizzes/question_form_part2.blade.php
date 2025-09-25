@extends('layouts.app')

@section('title', isset($question->id) ? 'Sửa Reading Part 2' : 'Thêm Reading Part 2')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
            <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
                {{ isset($question->id) ? 'Sửa' : 'Tạo' }} Reading Part 2 — Ordering
                @if(isset($question->reading_set_id) && isset($sets))
                    @php $setObj = $sets->firstWhere('id', $question->reading_set_id); @endphp
                    @if($setObj)
                        <span
                            class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 border border-blue-200 ml-2">
                            {{ ucfirst($setObj->skill) }}
                        </span>
                    @endif
                @endif
            </h1>

            <div class="text-sm text-gray-500 mb-3">Các trường có dấu <span class="text-red-500">*</span> là bắt buộc.</div>

            @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                action="{{ isset($question->id) ? route('admin.questions.part2.update', $question) : route('admin.questions.part2.store') }}"
                class="space-y-4">
                @csrf
                @if(isset($question->id))
                    @method('PUT')
                @endif

                @php
                    $readingSetId = old('reading_set_id', $question->reading_set_id ?? request('reading_set_id'));
                    $setObj = $sets->firstWhere('id', $readingSetId);
                    $quizId = $setObj ? $setObj->quiz_id : old('quiz_id', $question->quiz_id ?? request('quiz_id'));
                    $quizObj = $quizzes->firstWhere('id', $quizId);
                @endphp
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-base font-semibold text-slate-700 mb-1">Quiz</label>
                        <input type="hidden" name="quiz_id" value="{{ $quizObj->id }}">
                        <div
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-blue-700 font-semibold shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40">
                            {{ $quizObj->title }}</div>
                    </div>
                    <div>
                        <label class="block text-base font-semibold text-slate-700 mb-1">Set</label>
                        <input type="hidden" name="reading_set_id" value="{{ $setObj->id }}">
                        <div
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-emerald-700 font-semibold shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40">
                            {{ $setObj->title }}</div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-base font-semibold text-slate-700 mb-1">Tiêu đề (stem) <span
                            class="text-red-500">*</span></label>
                    <textarea name="stem" rows="3"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40">{{ old('stem', $question->stem) }}</textarea>
                    @error('stem')<div class="text-red-500 text-base">{{ $message }}</div>@enderror
                </div>

                <div class="mb-8">
                    <label class="block text-base font-semibold text-slate-700 mb-2">Nhập các câu (thứ tự đúng, đáp án)
                        <span class="text-red-500">*</span></label>
                    <div id="sentences-input-list" class="grid grid-cols-1 gap-2">
                        @php
                            $oldSentences = old('sentences', $question->metadata['sentences'] ?? []);
                            $count = max(4, count($oldSentences));
                        @endphp
                        @for($i = 0; $i < $count; $i++)
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-blue-700">{{ $i + 1 }}.</span>
                                <textarea name="sentences[]" rows="3"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 sentence-input">{{ $oldSentences[$i] ?? '' }}</textarea>
                            </div>
                            @error('sentences.' . $i)<div class="text-red-500 text-base mb-2">{{ $message }}</div>@enderror
                        @endfor
                    </div>
                    <small class="text-gray-500 text-base">Nhập các câu theo thứ tự đúng (đáp án).</small>
                </div>

                <div class="mb-8">
                    <label class="block text-base font-semibold mb-2">Kéo thả để trộn thứ tự hiển thị ban đầu</label>
                    <div id="sentences-shuffle-list" class="grid grid-cols-1 gap-2">
                        @php
                            $displayOrder = old('display_order');
                            if ($displayOrder === null && isset($question->metadata['display_order'])) {
                                $displayOrder = $question->metadata['display_order'];
                            }
                            if ($displayOrder === null) {
                                $displayOrder = range(0, $count - 1);
                            } elseif (is_string($displayOrder)) {
                                $displayOrder = explode(',', $displayOrder);
                            }
                            // Nếu vừa nhập lại sentences (old('sentences')), reset displayOrder về 0,1,2,3...
                            if (old('sentences')) {
                                $displayOrder = range(0, $count - 1);
                            }
                        @endphp
                        @foreach($displayOrder as $i => $idx)
                            <div class="flex items-center gap-2 sentence-shuffle-item bg-slate-50 rounded-xl p-2 cursor-move border border-slate-200"
                                data-idx="{{ $idx }}">
                                <span class="font-semibold drag-handle text-slate-500">&#9776;</span>
                                <span
                                    class="sentence-shuffle-text text-base text-slate-800">{{ $oldSentences[$idx] ?? '' }}</span>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="display_order" id="display_order_input"
                        value="{{ implode(',', $displayOrder) }}">
                    <input type="hidden" name="correct_order" value="{{ implode(',', range(0, $count - 1)) }}">
                    <small class="text-gray-500 text-base">Kéo thả các câu để trộn thứ tự hiển thị ban đầu cho học
                        sinh.</small>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var sortableEl = document.getElementById('sentences-shuffle-list');
                        var displayOrderInput = document.getElementById('display_order_input');
                        // Reset displayOrder về 0,1,2,3... khi nhập lại đáp án
                        var sentenceInputs = document.querySelectorAll('.sentence-input');
                        sentenceInputs.forEach(function (input) {
                            input.addEventListener('input', function () {
                                var items = sortableEl.querySelectorAll('.sentence-shuffle-item');
                                items.forEach(function (item, idx) {
                                    item.setAttribute('data-idx', idx);
                                    item.querySelector('.sentence-shuffle-text').textContent = document.querySelectorAll('.sentence-input')[idx].value;
                                });
                                var order = Array.from(items).map(function (item, idx) { return idx; });
                                displayOrderInput.value = order.join(',');
                            });
                        });
                        if (sortableEl) {
                            new Sortable(sortableEl, {
                                handle: '.drag-handle',
                                animation: 150,
                                onSort: function (evt) {
                                    var items = sortableEl.querySelectorAll('.sentence-shuffle-item');
                                    var order = Array.from(items).map(function (item) {
                                        return item.getAttribute('data-idx');
                                    });
                                    displayOrderInput.value = order.join(',');
                                }
                            });
                        }
                    });
                </script>

                <div class="flex justify-end mt-6 gap-4">
                    <a href="{{ route('admin.quizzes.questions') }}"
                        class="inline-flex items-center px-6 py-3 bg-white border border-slate-300 rounded-xl font-semibold text-base text-blue-700 shadow hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500/60 transition">
                        Quay lại
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-xl font-semibold text-base text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/60 transition">
                        {{ isset($question->id) ? 'Cập nhật' : 'Tạo mới' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection