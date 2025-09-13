<div class="w-full max-w-3xl mx-auto p-4">
	<div class="mb-4">
		<h2 class="text-lg font-semibold text-gray-800">Câu hỏi {{ $question->order_no }}</h2>
		@if(!empty($question->stem))
			<p class="text-gray-800 font-medium">{{ $question->stem }}</p>
		@endif
		@if(!empty($question->content))
			<p class="text-gray-700 mt-1">{{ $question->content }}</p>
		@endif
	</div>

	<form class="space-y-3">
		@foreach($question->metadata['options'] as $idx => $option)
			<label class="flex items-start space-x-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
				<input type="radio" name="selected" value="{{ $idx }}" class="mt-1 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
				<span class="text-gray-800">{!! $option !!}</span>
			</label>
		@endforeach
	</form>

	<div class="mt-6 flex items-center space-x-4">
		<button type="button" class="px-5 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700" onclick="submitListeningAnswerPart({{ $question->id }})">Kiểm tra</button>
		<button type="button" class="px-5 py-2 bg-gray-300 text-gray-800 rounded-lg shadow hover:bg-gray-400" disabled id="next-btn-{{ $question->id }}">Next</button>
	</div>

	<div id="feedback-{{ $question->id }}" class="mt-4 hidden"></div>
	<div class="inline-feedback mt-3 text-sm text-gray-700" data-qid-feedback="{{ $question->id }}"></div>
</div>

<script>
function submitListeningAnswerPart(questionId) {
	const selected = document.querySelector(`input[name="selected"]:checked`);
	const feedback = document.getElementById(`feedback-${questionId}`);
	feedback.className = 'mt-4 hidden';

	if (!selected) {
		feedback.className = 'mt-4 bg-yellow-50 border border-yellow-300 p-3 rounded text-yellow-800';
		feedback.innerHTML = `<p class="font-medium">Vui lòng chọn một đáp án trước khi kiểm tra.</p>`;
		return;
	}

	try {
		const meta = @json($question->metadata);
		const sel = parseInt(selected.value, 10);
		let isCorrect = false;
		let correct = null;
		if (meta.hasOwnProperty('correct_index')) {
			correct = parseInt(meta.correct_index, 10);
			isCorrect = sel === correct;
		} else if (meta.hasOwnProperty('correct')) {
			const arr = Array.isArray(meta.correct) ? meta.correct.map(Number) : [Number(meta.correct)];
			correct = arr;
			isCorrect = arr.includes(sel);
		}

		feedback.classList.remove('hidden');
		if (isCorrect) {
			feedback.className = 'mt-4 bg-green-50 border border-green-300 p-3 rounded text-green-800';
			feedback.innerHTML = `<p class="font-semibold">Chính xác!</p>`;
		} else {
			feedback.className = 'mt-4 bg-red-50 border border-red-300 p-3 rounded text-red-800';
			let correctText = correct ?? '—';
			if (Array.isArray(correctText)) correctText = correctText.join(', ');
			feedback.innerHTML = `<p class="font-semibold">Sai.</p><p class="text-sm mt-1">Đáp án đúng: ${correctText}</p>`;
		}
		document.getElementById(`next-btn-${questionId}`).disabled = false;

		// store canonical payload
		try {
			window.attemptAnswers = window.attemptAnswers || {};
			window.attemptAnswers[questionId] = { part: 'select', value: sel, is_correct: isCorrect };
			try { localStorage.setItem('attempt_answers_' + (window.currentAttemptId || {{ $question->quiz_id ?? 0 }}), JSON.stringify(window.attemptAnswers)); } catch(e) {}
		} catch(e) { console.warn(e); }
	} catch (e) {
		console.error(e);
		feedback.className = 'mt-4 bg-red-50 border border-red-300 p-3 rounded text-red-800';
		feedback.innerHTML = `<p class="font-semibold">Có lỗi xảy ra. Vui lòng thử lại.</p>`;
	}
}
</script>
