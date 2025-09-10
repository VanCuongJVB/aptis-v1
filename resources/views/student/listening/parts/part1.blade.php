<div class="w-full max-w-3xl mx-auto p-4">
    {{-- Audio area --}}
    <div class="mb-4 flex items-center space-x-3">
        @if(!empty($question->metadata['audio_path']))
            <audio controls preload="none" class="w-full">
                <source src="{{ asset($question->metadata['audio_path']) }}" type="audio/mpeg">
                Trình duyệt của bạn không hỗ trợ audio.
            </audio>
        @else
            <div class="flex items-center justify-center w-12 h-12 bg-gray-200 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6h13M9 6l-7 7 7 7" />
                </svg>
            </div>
        @endif
    </div>

    {{-- Transcript (practice mode only) --}}
    @if(!empty($question->metadata['audio_text']))
        <div class="mb-4 p-3 bg-gray-50 border rounded text-sm text-gray-700">
            {{ $question->metadata['audio_text'] }}
        </div>
    @endif

    {{-- Question prompt --}}
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Câu hỏi {{ $question->order_no }}</h2>
        @if(!empty($question->content))
            <p class="text-gray-700 mt-1">{{ $question->content }}</p>
        @endif
    </div>

    {{-- Options --}}
    <form class="space-y-3">
        @foreach($question->metadata['options'] as $idx => $option)
            <label class="flex items-start space-x-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
                <input type="radio" 
                       name="selected_option_id" 
                       value="{{ $idx }}" 
                       class="mt-1 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                <span class="text-gray-800">{!! $option !!}</span>
            </label>
        @endforeach
    </form>

    {{-- Action buttons --}}
    <div class="mt-6 flex items-center space-x-4">
        <button type="button" 
                class="px-5 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700"
                onclick="submitListeningAnswer({{ $question->id }})">
            Kiểm tra
        </button>
        <button type="button" 
                class="px-5 py-2 bg-gray-300 text-gray-800 rounded-lg shadow hover:bg-gray-400"
                disabled
                id="next-btn-{{ $question->id }}">
            Next
        </button>
    </div>

    {{-- Feedback --}}
    <div id="feedback-{{ $question->id }}" class="mt-4 hidden"></div>
</div>

<script>
function submitListeningAnswer(questionId) {
    const selected = document.querySelector(`input[name="selected_option_id"]:checked`);
    const feedback = document.getElementById(`feedback-${questionId}`);
    // reset feedback
    feedback.className = 'mt-4 hidden';

    if (!selected) {
        feedback.className = 'mt-4 bg-yellow-50 border border-yellow-300 p-3 rounded text-yellow-800';
        feedback.innerHTML = `<p class="font-medium">Vui lòng chọn một đáp án trước khi kiểm tra.</p>`;
        return;
    }

    // Grade locally using metadata (no server call)
    try {
        const meta = @json($question->metadata);
        const selectedIndex = parseInt(selected.value, 10);
        const result = (function grade(meta, sel) {
            // Multiple choice with single correct_index
            if (meta.hasOwnProperty('correct_index')) {
                const correct = parseInt(meta.correct_index, 10);
                return { is_correct: sel === correct, correct: correct };
            }

            // If metadata has 'correct' as an array of correct indexes
            if (meta.hasOwnProperty('correct')) {
                const correctArr = Array.isArray(meta.correct) ? meta.correct.map(Number) : [Number(meta.correct)];
                return { is_correct: correctArr.includes(sel), correct: correctArr };
            }

            // Fallback: not enough metadata
            return { is_correct: false, correct: null };
        })(meta, selectedIndex);

        feedback.classList.remove('hidden');
        if (result.is_correct) {
            feedback.className = 'mt-4 bg-green-50 border border-green-300 p-3 rounded text-green-800';
            feedback.innerHTML = `<p class="font-semibold">Chính xác!</p>`;
        } else {
            feedback.className = 'mt-4 bg-red-50 border border-red-300 p-3 rounded text-red-800';
            let correct = result.correct ?? '—';
            if (Array.isArray(correct)) correct = correct.join(', ');
            feedback.innerHTML = `<p class="font-semibold">Sai.</p><p class="text-sm mt-1">Đáp án đúng: ${correct}</p>`;
        }

        document.getElementById(`next-btn-${questionId}`).disabled = false;

        // store answer locally (to be submitted in one batch at the end)
        try {
            window.attemptAnswers = window.attemptAnswers || {};
            window.attemptAnswers[questionId] = { selected: selectedIndex, is_correct: result.is_correct };
            console.log('stored local answer', window.attemptAnswers[questionId]);
        } catch(e) { console.warn(e); }
    } catch (e) {
        console.error(e);
        feedback.className = 'mt-4 bg-red-50 border border-red-300 p-3 rounded text-red-800';
        feedback.innerHTML = `<p class="font-semibold">Có lỗi xảy ra. Vui lòng thử lại.</p>`;
    }
}
</script>
