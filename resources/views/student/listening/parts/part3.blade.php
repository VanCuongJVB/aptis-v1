<div class="w-full max-w-3xl mx-auto p-4 question-block mb-6" data-qid="{{ $question->id }}"
	data-part="{{ $question->part ?? 3 }}"
	data-metadata='@json($question->metadata)'>

	{{-- Audio area --}}
	@if(!empty($audioUrl))
		<div class="mb-4">
			<audio controls preload="metadata" class="w-full" crossorigin="anonymous" playsinline>
				<source src="{{ $audioUrl }}" type="audio/mpeg">
				Trình duyệt của bạn không hỗ trợ audio.
			</audio>
		</div>
	@endif
	<div class="mb-4">
		<h2 class="text-lg font-semibold text-gray-800">Câu hỏi {{ $question->order_no }}</h2>
		@if(!empty($question->stem))
			<p class="text-gray-800 font-semibold mt-1">{{ $question->stem }}</p>
		@endif
		@if(!empty($question->content))
			<p class="text-gray-700 mt-1">{{ $question->content }}</p>
		@endif
		@php $desc = $question->metadata['description'] ?? null; @endphp
		@if(!empty($desc))
			<div class="mb-4">
				<a href="#" id="desc-toggle-{{ $question->id }}" data-qid="{{ $question->id }}" class="desc-toggle-link text-blue-600 underline text-sm">Hiển thị mô tả</a>
				<div id="desc-box-{{ $question->id }}" class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded text-gray-800 text-sm" style="display:none;">
					{!! nl2br(e($desc)) !!}
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
			</div>
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
					<span class="font-medium">{{ $speaker['label'] ?? ('Người nói ' . ($spIdx + 1)) }}</span>
					@if(!empty($speaker['description']))
						<button type="button" class="ml-2 text-blue-500 underline text-xs toggle-desc-btn"
							data-speaker-idx="{{ $spIdx }}">[Xem mô tả]</button>
						<span class="speaker-desc text-gray-600 text-sm ml-2 hidden"
							id="desc-{{ $question->id }}-{{ $spIdx }}">{{ $speaker['description'] }}</span>
					@endif
				</div>
			@endforeach
		</div>
	@endif

</div>

@push('scripts')
	<script>
		document.addEventListener('click', function (e) {
			// Speaker description toggle
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
			// Main description toggle (part 3)
			if (e.target && e.target.classList.contains('desc-toggle-link')) {
				const btn = e.target;
				const qid = btn.id.replace('desc-toggle-', '');
				const descBox = document.getElementById('desc-box-' + qid);
				if (descBox) {
					descBox.classList.toggle('hidden');
					btn.textContent = descBox.classList.contains('hidden') ? 'Hiển thị mô tả' : 'Ẩn mô tả';
				}
			}
		});

		// Audio play handler for Safari compatibility
		document.addEventListener('DOMContentLoaded', function() {
			const audios = document.querySelectorAll('audio');
			audios.forEach(audio => {
				audio.addEventListener('play', function(e) {
					const playPromise = audio.play();
					if (playPromise !== undefined) {
						playPromise.catch(error => {
							console.error('Audio play error:', error);
						});
					}
				}, false);
			});
		});
	</script>
@endpush