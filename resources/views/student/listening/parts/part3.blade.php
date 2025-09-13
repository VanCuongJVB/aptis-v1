<div class="w-full max-w-3xl mx-auto p-4 question-block" data-qid="{{ $question->id }}" data-metadata="{{ htmlspecialchars(json_encode($question->metadata)) }}">
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

	<div id="feedback-{{ $question->id }}" class="mt-4 hidden"></div>
	<div class="inline-feedback mt-3 text-sm text-gray-700" data-qid-feedback="{{ $question->id }}"></div>
</div>
