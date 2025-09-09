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
    if (!selected) {
        alert("Vui lòng chọn một đáp án trước khi kiểm tra.");
        return;
    }

    fetch("{{ route('student.listening.submit', $question->id) }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "X-Requested-With": "XMLHttpRequest",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            action: "submit",
            selected_option_id: selected.value
        })
    })
    .then(res => res.json())
    .then(data => {
        const feedback = document.getElementById(`feedback-${questionId}`);
        feedback.classList.remove("hidden");
        if (data.is_correct) {
            feedback.innerHTML = `<p class="text-green-600 font-semibold">Chính xác!</p>`;
        } else {
            feedback.innerHTML = `<p class="text-red-600 font-semibold">Sai. Đáp án đúng là: ${data.correct}</p>`;
        }
        document.getElementById(`next-btn-${questionId}`).disabled = false;
    })
    .catch(err => console.error(err));
}
</script>
