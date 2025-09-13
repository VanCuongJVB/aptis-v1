<div class="w-full max-w-3xl mx-auto p-4">
	<div class="mb-4">
		<h2 class="text-lg font-semibold text-gray-800">Câu hỏi {{ $question->order_no }}</h2>
		@if(!empty($question->metadata['title']))
			<p class="text-gray-800 font-medium">{{ $question->metadata['title'] }}</p>
		@endif
		@if(!empty($question->content))
			<p class="text-gray-700 mt-1">{{ $question->content }}</p>
		@endif
	</div>

	<form class="space-y-3">
		@foreach($question->metadata['items'] as $idx => $item)
			<div class="p-3 border rounded-lg">
				<div class="mb-2 text-gray-800">{{ $idx + 1 }}. {!! $item !!}</div>
				<select name="select-{{ $idx }}" class="w-full border rounded p-2" data-idx="{{ $idx }}">
					<option value="">-- Chọn --</option>
					@foreach($question->metadata['options'] as $optIdx => $opt)
						<option value="{{ $optIdx }}">{{ $opt }}</option>
					@endforeach
				</select>
				<div class="mt-2 text-sm text-gray-600" id="item-feedback-{{ $question->id }}-{{ $idx }}"></div>
			</div>
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
	const feedback = document.getElementById(`feedback-${questionId}`);
	feedback.className = 'mt-4 hidden';

	try {
		const meta = @json($question->metadata);
		const selects = Array.from(document.querySelectorAll(`select[name^="select-"]`));
		if (selects.length === 0) {
			feedback.className = 'mt-4 bg-yellow-50 border border-yellow-300 p-3 rounded text-yellow-800';
			feedback.innerHTML = `<p class="font-medium">Không có mục để kiểm tra.</p>`;
			return;
		}

		// collect chosen indices (null for unanswered)
		const values = selects.map(s => {
			const v = s.value;
			return v === '' ? null : Number(v);
		});

		// evaluate per-item correctness using meta.answers (array)
		const answers = Array.isArray(meta.answers) ? meta.answers.map(v => (isNaN(v) ? v : Number(v))) : [];
		const itemFeedbacks = [];
		let allAnswered = true;
		let allCorrect = true;

		for (let i = 0; i < values.length; i++) {
			const chosen = values[i];
			const correct = answers[i] !== undefined ? answers[i] : null;
			const fbEl = document.getElementById(`item-feedback-${questionId}-${i}`);
			if (chosen === null) {
				allAnswered = false;
				fbEl.innerHTML = `<span class="text-yellow-600">Chưa chọn đáp án.</span>`;
			} else if (correct === null) {
				fbEl.innerHTML = `<span class="text-gray-600">Không có đáp án tham chiếu.</span>`;
			} else if (chosen === Number(correct)) {
				fbEl.innerHTML = `<span class="text-green-600 font-semibold">Chính xác</span>`;
			} else {
				allCorrect = false;
				const correctText = (Array.isArray(meta.options) && meta.options[correct] !== undefined) ? meta.options[correct] : String(correct);
				fbEl.innerHTML = `<span class="text-red-600">Sai. Đáp án đúng: ${correctText}</span>`;
			}
		}

		// general feedback
		feedback.classList.remove('hidden');
		if (!allAnswered) {
			feedback.className = 'mt-4 bg-yellow-50 border border-yellow-300 p-3 rounded text-yellow-800';
			feedback.innerHTML = `<p class="font-medium">Bạn chưa trả lời tất cả các mục. Vui lòng hoàn thành để chấm điểm đầy đủ.</p>`;
		} else if (allCorrect) {
			feedback.className = 'mt-4 bg-green-50 border border-green-300 p-3 rounded text-green-800';
			feedback.innerHTML = `<p class="font-semibold">Tất cả đúng!` + `</p>`;
		} else {
			feedback.className = 'mt-4 bg-red-50 border border-red-300 p-3 rounded text-red-800';
			feedback.innerHTML = `<p class="font-semibold">Có một số đáp án chưa đúng.</p>`;
		}

		document.getElementById(`next-btn-${questionId}`).disabled = false;

		// store canonical payload
		try {
			window.attemptAnswers = window.attemptAnswers || {};
			window.attemptAnswers[questionId] = { part: 'part3', values: values, selected: values, is_correct: allAnswered ? allCorrect : false };
			try { localStorage.setItem('attempt_answers_' + (window.currentAttemptId || {{ $question->quiz_id ?? 0 }}), JSON.stringify(window.attemptAnswers)); } catch (e) {}
		} catch (e) { console.warn(e); }
	} catch (e) {
		console.error(e);
		feedback.className = 'mt-4 bg-red-50 border border-red-300 p-3 rounded text-red-800';
		feedback.innerHTML = `<p class="font-semibold">Có lỗi xảy ra. Vui lòng thử lại.</p>`;
	}
}
</script>
