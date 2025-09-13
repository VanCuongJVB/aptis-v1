<div class="w-full max-w-3xl mx-auto p-4 question-block" data-qid="{{ $question->id }}" data-metadata="{{ htmlspecialchars(json_encode($question->metadata)) }}">
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
			<label class="flex items-start space-x-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer mb-2">
				<input type="radio" name="selected" value="{{ $idx }}" class="mt-1 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
				<span class="text-gray-800">{!! $option !!}</span>
			</label>
		@endforeach
	</form>

	<div id="feedback-{{ $question->id }}" class="mt-4 hidden"></div>
	<div class="inline-feedback mt-3 text-sm text-gray-700" data-qid-feedback="{{ $question->id }}"></div>
</div>
