<div class="w-full max-w-3xl mx-auto p-4 question-block" data-qid="{{ $question->id }}" data-metadata="{{ htmlspecialchars(json_encode($question->metadata)) }}">
    {{-- Audio area --}}

    @if(!empty($question->metadata['audio_text']))
        <div class="mb-4 p-3 bg-gray-50 border rounded text-sm text-gray-700">
            {{ $question->metadata['audio_text'] }}
        </div>
    @endif

    {{-- Question prompt --}}
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-gray-800">
            Câu hỏi {{ $question->order_no ?? '' }}
        </h2>
        @if(!empty($question->stem))
            <p class="text-gray-800 font-medium mt-1">
                {{ $question->stem }}
            </p>
        @endif
        @if(!empty($question->content))
            <p class="text-gray-700 mt-1">{{ $question->content }}</p>
        @endif
    </div>


    {{-- Description toggle --}}
    @php $desc = $question->metadata['description'] ?? null; @endphp
    @if(!empty($desc))
        <div class="mb-4">
            <a href="#" id="desc-toggle-{{ $question->id }}" data-qid="{{ $question->id }}" class="desc-toggle-link text-blue-600 underline text-sm">Hiển thị mô tả</a>
            <div id="desc-box-{{ $question->id }}" class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded text-gray-800 text-sm" style="display:none;">
                {{ $desc }}
            </div>
        </div>
        <script>
            document.addEventListener('click', function(e) {
                var btn = e.target;
                if (btn.matches('.desc-toggle-link')) {
                    e.preventDefault();
                    var qid = btn.getAttribute('data-qid');
                    var box = document.getElementById('desc-box-' + qid);
                    if (box) {
                        if (box.style.display === 'none' || box.style.display === '') {
                            box.style.display = 'block';
                            btn.textContent = 'Ẩn mô tả';
                        } else {
                            box.style.display = 'none';
                            btn.textContent = 'Hiển thị mô tả';
                        }
                    }
                }
            });
        </script>
    @endif

    {{-- Options --}}
    <form class="space-y-3">
        @php
            $options = $question->metadata['options'];
        @endphp
        @foreach($options as $idx => $opt)
            <label class="flex items-start space-x-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer mb-2">
                <input type="radio" name="selected_option_id" value="{{ $idx }}"
                    class="mt-1 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                <span class="text-gray-800">{!! $opt !!}</span>
            </label>
        @endforeach
    </form>

    {{-- Feedback --}}
    <div id="feedback-{{ $question->id }}" class="mt-4 hidden"></div>
    <div class="inline-feedback mt-3 text-sm text-gray-700" data-qid-feedback="{{ $question->id }}"></div>
</div>