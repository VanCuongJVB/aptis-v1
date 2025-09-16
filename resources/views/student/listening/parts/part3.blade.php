
<div class="w-full max-w-3xl mx-auto p-4 question-block" data-qid="{{ $question->id }}" data-metadata="{{ htmlspecialchars(json_encode($question->metadata)) }}">
	<div class="mb-4">
		<h2 class="text-lg font-semibold text-gray-800">Câu hỏi {{ $question->order_no }}</h2>
		@if(!empty($question->stem))
			<p class="text-gray-800 font-semibold mt-1">{{ $question->stem }}</p>
		@endif
		@if(!empty($question->content))
			<p class="text-gray-700 mt-1">{{ $question->content }}</p>
		@endif
	</div>

	<form class="space-y-3">
		@foreach($question->metadata['items'] as $idx => $item)
			<div class="p-3 border rounded-lg">
				<div class="mb-2 text-gray-800">{{ $idx + 1 }}. {!! $item !!}</div>
				<select name="part3_answer[{{ $idx }}]" class="w-full border rounded p-2" data-idx="{{ $idx }}">
					<option value="">-- Chọn --</option>
					@foreach($question->metadata['options'] as $optIdx => $opt)
						<option value="{{ $optIdx }}">{{ $opt }}</option>
					@endforeach
				</select>
				<div class="mt-2 text-sm text-gray-600" id="item-feedback-{{ $question->id }}-{{ $idx }}"></div>
			</div>
		@endforeach

	</form>

	@php
	$speakers = $question->metadata['speakers'] ?? [];
	@endphp
	@if(!empty($speakers))
	<div class="mt-6 speakers-list border-t pt-4">
		<div class="font-semibold text-gray-700 mb-2">Người nói:</div>
		@foreach($speakers as $spIdx => $speaker)
			<div class="mb-2 p-2 bg-gray-50 rounded border">
				<span class="font-medium">{{ $speaker['label'] ?? ('Người nói ' . ($spIdx+1)) }}</span>
				@if(!empty($speaker['description']))
					<button type="button" class="ml-2 text-blue-500 underline text-xs toggle-desc-btn" data-speaker-idx="{{ $spIdx }}">[Xem mô tả]</button>
					<span class="speaker-desc text-gray-600 text-sm ml-2 hidden" id="desc-{{ $question->id }}-{{ $spIdx }}">{{ $speaker['description'] }}</span>
				@endif
			</div>
		@endforeach
	</div>
	@endif

	<div id="feedback-{{ $question->id }}" class="mt-4 hidden"></div>
	<div class="inline-feedback mt-3 text-sm text-gray-700" data-qid-feedback="{{ $question->id }}"></div>

	<script>
	// Event delegation for toggle-desc-btn (works after AJAX/next question)
	document.addEventListener('click', function(e) {
		if (e.target && e.target.classList.contains('toggle-desc-btn')) {
			const btn = e.target;
			const questionBlock = btn.closest('.question-block');
			const idx = btn.getAttribute('data-speaker-idx');
			const qid = questionBlock?.getAttribute('data-qid');
			const desc = questionBlock?.querySelector(`#desc-${qid}-${idx}`);
			if (desc) {
				desc.classList.toggle('hidden');
				btn.textContent = desc.classList.contains('hidden') ? '[Xem mô tả]' : '[Ẩn mô tả]';
			}
		}
	});
	</script>
</div>
