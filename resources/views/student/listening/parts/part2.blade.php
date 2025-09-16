<div class="w-full max-w-3xl mx-auto p-4 question-block" data-qid="{{ $question->id }}" data-metadata="{{ htmlspecialchars(json_encode($question->metadata)) }}">
	<div class="mb-4">
		<h2 class="text-lg font-semibold text-gray-800">Câu hỏi {{ $question->order_no }}</h2>
		@if(!empty($question->stem))
			<p class="text-gray-700 mt-1">{{ $question->stem }}</p>
		@endif
	</div>

	<form class="space-y-3">
		@php
			$speakers = $question->metadata['speakers'] ?? [];
			$options = $question->metadata['options'] ?? [];
			
			$optionIndices = array_keys($options);
			
			$indexMapping = [];
			foreach ($optionIndices as $newIdx => $originalIdx) {
				$indexMapping[$newIdx] = $originalIdx;
			}
			
			$metadataWithMapping = $question->metadata;
			$metadataWithMapping['optionMapping'] = $indexMapping;
		@endphp

		@foreach($speakers as $idx => $speaker)
			<div class="p-3 border rounded-md">
				<div class="text-sm font-medium mb-2 flex items-center">
					{{ $speaker['label'] }}
					@if(!empty($speaker['description']))
						<button type="button" class="ml-2 text-blue-500 underline text-xs toggle-desc-btn" data-speaker-idx="{{ $idx }}">[Xem mô tả]</button>
					@endif
				</div>
				@if(!empty($speaker['description']))
					<div class="speaker-desc text-gray-600 text-sm mb-2 hidden" id="desc-{{ $question->id }}-{{ $idx }}">{{ $speaker['description'] }}</div>
				@endif
				<select class="w-full border rounded p-2 speaker-select part2-select" data-index="{{ $idx }}">
					<option value="">- Chọn câu mô tả -</option>
					@foreach($optionIndices as $newIdx => $originalIdx)
						<option value="{{ $newIdx }}" data-original-index="{{ $originalIdx }}">{{ e($options[$originalIdx]) }}</option>
					@endforeach
				</select>
			</div>
		@endforeach
	</form>

	<div id="feedback-{{ $question->id }}" class="mt-4 hidden"></div>
	<div class="inline-feedback mt-3 text-sm text-gray-700" data-qid-feedback="{{ $question->id }}"></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
	const questionBlock = document.querySelector(`.question-block[data-qid="{{ $question->id }}"]`);
	if (questionBlock) {
		try {
			const metadata = JSON.parse(questionBlock.getAttribute('data-metadata') || '{}');
			metadata.optionMapping = @json($indexMapping);
			questionBlock.setAttribute('data-metadata', JSON.stringify(metadata));
		} catch (e) {
			console.error('Error updating metadata:', e);
		}
	}
});

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