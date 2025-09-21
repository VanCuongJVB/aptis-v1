@extends('layouts.app')

@section('title', 'Tạo câu hỏi Part 2 (Ordering)')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded shadow p-6">
        <h1 class="text-xl font-semibold mb-4">Tạo câu hỏi Part 2 (Ordering)</h1>
        <form method="POST" action="{{ isset($question->id) ? route('admin.questions.part2.update', $question) : route('admin.questions.part2.store') }}">
            @csrf
            @if(isset($question->id))
                @method('PUT')
            @endif

            <div class="mb-4">
                <label class="block font-medium mb-1">Quiz</label>
                <select name="quiz_id" class="form-select w-full">
                    <option value="">-- Chọn quiz --</option>
                    @foreach($quizzes as $quiz)
                        <option value="{{ $quiz->id }}" {{ old('quiz_id', $question->quiz_id) == $quiz->id ? 'selected' : '' }}>{{ $quiz->title }}</option>
                    @endforeach
                </select>
                @error('quiz_id')<div class="text-red-500 text-sm">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Set</label>
                <select name="reading_set_id" class="form-select w-full">
                    <option value="">-- Chọn set --</option>
                    @foreach($sets as $set)
                        <option value="{{ $set->id }}" {{ old('reading_set_id', $question->reading_set_id) == $set->id ? 'selected' : '' }}>{{ $set->title }}</option>
                    @endforeach
                </select>
                @error('reading_set_id')<div class="text-red-500 text-sm">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Tiêu đề (stem)</label>
                <input type="text" name="stem" class="form-input w-full" value="{{ old('stem', $question->stem) }}">
                @error('stem')<div class="text-red-500 text-sm">{{ $message }}</div>@enderror
            </div>

            <div class="mb-6">
                <label class="block font-medium mb-2">Nhập các câu (thứ tự đúng, đáp án)</label>
                <div id="sentences-input-list" class="space-y-2">
                    @php
                        $oldSentences = old('sentences', $question->metadata['sentences'] ?? []);
                        $count = max(4, count($oldSentences));
                    @endphp
                    @for($i = 0; $i < $count; $i++)
                        <div class="flex items-center mb-2">
                            <span class="mr-2 font-semibold">{{ $i+1 }}.</span>
                            <input type="text" name="sentences[]" class="form-input w-full sentence-input" value="{{ $oldSentences[$i] ?? '' }}">
                        </div>
                        @error('sentences.'.$i)<div class="text-red-500 text-xs mb-2">{{ $message }}</div>@enderror
                    @endfor
                </div>
                <small class="text-gray-500">Nhập các câu theo thứ tự đúng (đáp án).</small>
            </div>

            <div class="mb-6">
                <label class="block font-medium mb-2">Kéo thả để trộn thứ tự hiển thị ban đầu</label>
                <div id="sentences-shuffle-list" class="space-y-2">
                    @php
                        $displayOrder = old('display_order');
                        if ($displayOrder === null && isset($question->metadata['display_order'])) {
                            $displayOrder = $question->metadata['display_order'];
                        }
                        if ($displayOrder === null) {
                            $displayOrder = range(0, $count-1);
                        } elseif (is_string($displayOrder)) {
                            $displayOrder = explode(',', $displayOrder);
                        }
                        // Nếu vừa nhập lại sentences (old('sentences')), reset displayOrder về 0,1,2,3...
                        if (old('sentences')) {
                            $displayOrder = range(0, $count-1);
                        }
                    @endphp
                    @foreach($displayOrder as $i => $idx)
                        <div class="flex items-center mb-2 sentence-shuffle-item bg-gray-50 rounded p-2 cursor-move" data-idx="{{ $idx }}">
                            <span class="mr-2 font-semibold drag-handle">&#9776;</span>
                            <span class="sentence-shuffle-text">{{ $oldSentences[$idx] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
                <input type="hidden" name="display_order" id="display_order_input" value="{{ implode(',', $displayOrder) }}">
                <input type="hidden" name="correct_order" value="{{ implode(',', range(0, $count-1)) }}">
                <small class="text-gray-500">Kéo thả các câu để trộn thứ tự hiển thị ban đầu cho học sinh.</small>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var sortableEl = document.getElementById('sentences-shuffle-list');
                var displayOrderInput = document.getElementById('display_order_input');
                // Reset displayOrder về 0,1,2,3... khi nhập lại đáp án
                var sentenceInputs = document.querySelectorAll('.sentence-input');
                sentenceInputs.forEach(function(input) {
                    input.addEventListener('input', function() {
                        var items = sortableEl.querySelectorAll('.sentence-shuffle-item');
                        items.forEach(function(item, idx) {
                            item.setAttribute('data-idx', idx);
                            item.querySelector('.sentence-shuffle-text').textContent = document.querySelectorAll('.sentence-input')[idx].value;
                        });
                        var order = Array.from(items).map(function(item, idx) { return idx; });
                        displayOrderInput.value = order.join(',');
                    });
                });
                if (sortableEl) {
                    new Sortable(sortableEl, {
                        handle: '.drag-handle',
                        animation: 150,
                        onSort: function (evt) {
                            var items = sortableEl.querySelectorAll('.sentence-shuffle-item');
                            var order = Array.from(items).map(function(item) {
                                return item.getAttribute('data-idx');
                            });
                            displayOrderInput.value = order.join(',');
                        }
                    });
                }
            });
            </script>
            <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var shuffleEl = document.getElementById('sentences-shuffle-list');
                var displayOrderInput = document.getElementById('display_order_input');
                function updateShuffleTexts() {
                    // Cập nhật text trong vùng shuffle khi admin sửa input
                    var inputEls = document.querySelectorAll('#sentences-input-list input[name="sentences[]"]');
                    var shuffleItems = document.querySelectorAll('.sentence-shuffle-item');
                    shuffleItems.forEach(function(item, i) {
                        var idx = item.getAttribute('data-idx');
                        item.querySelector('.sentence-shuffle-text').textContent = inputEls[idx] ? inputEls[idx].value : '';
                    });
                }
                if (shuffleEl) {
                    new Sortable(shuffleEl, {
                        handle: '.drag-handle',
                        animation: 150,
                        onSort: function (evt) {
                            var items = shuffleEl.querySelectorAll('.sentence-shuffle-item');
                            var order = Array.from(items).map(function(item) {
                                return item.getAttribute('data-idx');
                            });
                            displayOrderInput.value = order.join(',');
                        }
                    });
                }
                // Khi admin sửa input, cập nhật text vùng shuffle
                document.getElementById('sentences-input-list').addEventListener('input', updateShuffleTexts);
                updateShuffleTexts();
            });
            </script>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">{{ isset($question->id) ? 'Cập nhật' : 'Tạo mới' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
