
@extends('layouts.app')

@section('title', isset($question->id) ? 'Sửa Reading Part 2' : 'Thêm Reading Part 2')

@section('content')
    <div class="container mx-auto px-2 py-8">
        <div class="max-w-7xl mx-auto bg-white rounded-3xl shadow-xl border border-slate-200 p-6 md:p-10">
            <h1 class="text-3xl font-extrabold mb-6 flex items-center gap-3 text-blue-800 tracking-tight">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                {{ isset($question->id) ? 'Sửa' : 'Tạo' }} <span class="text-blue-600">Reading Part 2</span> <span class="text-slate-500 text-lg font-normal">— Ordering</span>
            </h1>

            <p class="text-base text-slate-600 mb-5">Form quản trị <span class="font-semibold text-blue-700">Aptis Reading Part 2</span> (Ordering). Các trường có dấu <span class="text-red-500">*</span> là bắt buộc.</p>

            @if(session('success'))
                <div class="mb-4 p-3 bg-emerald-100 text-emerald-800 rounded-lg border border-emerald-200">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 p-3 bg-rose-100 text-rose-800 rounded-lg border border-rose-200">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <form method="POST"
                action="{{ isset($question->id) ? route('admin.questions.part2.update', $question) : route('admin.questions.part2.store') }}">
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-base font-semibold text-slate-700 mb-1">Quiz</label>
                        <div class="w-full rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-base text-blue-800 font-bold shadow-sm">{{ $quizObj->title }}</div>
                        <input type="hidden" name="quiz_id" value="{{ $quizObj->id }}">
                    </div>
                    <div>
                        <label class="block text-base font-semibold text-slate-700 mb-1">Set</label>
                        <div class="w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-base text-emerald-800 font-bold shadow-sm">{{ $setObj->title }}</div>
                        <input type="hidden" name="reading_set_id" value="{{ $setObj->id }}">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-base font-semibold text-slate-700 mb-1">Tiêu đề (stem) <span class="text-red-500">*</span></label>
                    <textarea name="stem" rows="3"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition">{{ old('stem', $question->stem) }}</textarea>
                    @error('stem')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                {{--
                    Lưu ý cho backend:
                    - Khi lưu, phải lấy đúng các trường:
                        + sentences: mảng các câu đúng thứ tự
                        + correct_order: mặc định là [0,1,2,3,...] hoặc lấy từ input hidden correct_order
                        + display_order: lấy từ input hidden display_order, là thứ tự trộn
                    - Lưu vào $question->metadata như sau:
                        $meta = [
                            'sentences' => $request->input('sentences', []),
                            'correct_order' => array_map('intval', explode(',', $request->input('correct_order'))),
                            'display_order' => array_map('intval', explode(',', $request->input('display_order'))),
                        ];
                        $question->metadata = $meta;
                --}}
                <div class="mb-10">
                    <label class="block text-base font-semibold text-slate-700 mb-2">Nhập các câu (thứ tự đúng, đáp án) <span class="text-red-500">*</span></label>
                    <div id="sentences-input-list" class="grid grid-cols-1 gap-2">
                        @php
                            $oldSentences = old('sentences', $question->metadata['sentences'] ?? []);
                            $count = max(4, count($oldSentences));
                        @endphp
                        @for($i = 0; $i < $count; $i++)
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-blue-700">{{ $i + 1 }}.</span>
                                <textarea name="sentences[]" rows="3"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition sentence-input">{{ $oldSentences[$i] ?? '' }}</textarea>
                            </div>
                            @error('sentences.' . $i)<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                        @endfor
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Nhập các câu theo thứ tự đúng (đáp án).</div>
                </div>

                <div class="mb-10">
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
                            if (old('sentences')) {
                                $displayOrder = range(0, $count - 1);
                            }
                        @endphp
                        @foreach($displayOrder as $i => $idx)
                            <div class="flex items-center gap-2 sentence-shuffle-item bg-slate-50 rounded-2xl p-2 cursor-move border border-slate-200"
                                data-idx="{{ $idx }}">
                                <span class="font-semibold drag-handle text-slate-500">&#9776;</span>
                                <span class="sentence-shuffle-text text-base text-slate-800">{{ $oldSentences[$idx] ?? '' }}</span>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="display_order" id="display_order_input"
                        value="{{ implode(',', $displayOrder) }}">
                    <input type="hidden" name="correct_order" value="{{ implode(',', range(0, $count - 1)) }}">
                    <div class="text-xs text-gray-500 mt-1">Kéo thả các câu để trộn thứ tự hiển thị ban đầu cho học sinh.</div>
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

                <div class="flex justify-end mt-8 gap-4">
                    <a href="{{ route('admin.quizzes.questions', ['part' => 2]) }}"
                        class="inline-flex items-center px-8 py-3 bg-white border border-slate-300 rounded-2xl font-semibold text-base text-blue-700 shadow hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500/60 transition">
                        <span class="px-2">Quay lại</span>
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-8 py-3 bg-blue-600 border border-transparent rounded-2xl font-semibold text-base text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/60 transition">
                        <span class="px-2">{{ isset($question->id) ? 'Cập nhật' : 'Tạo mới' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection