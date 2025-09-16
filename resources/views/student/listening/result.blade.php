@extends('layouts.app')

@section('title', 'Kết quả Listening')

@section('content')
<div class="container mx-auto py-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-lg font-semibold mb-2">Kết quả: {{ $quiz->title }}</h2>
        {{-- <div class="mb-4 text-sm text-gray-600">Bài làm bởi: bạn — Thời lượng: {{ $duration ?? 'N/A' }} phút</div> --}}

        <div class="mb-4 grid grid-cols-3 gap-4">
            <div class="p-3 bg-gray-50 rounded">
                <div class="text-xs text-gray-500">Tổng câu</div>
                <div class="text-xl font-bold">{{ $computedTotals['total'] ?? $attempt->total_questions ?? 0 }}</div>
            </div>
            <div class="p-3 bg-gray-50 rounded">
                <div class="text-xs text-gray-500">Đúng</div>
                <div class="text-xl font-bold">{{ $computedTotals['correct'] ?? $attempt->correct_answers ?? 0 }}</div>
            </div>
            <div class="p-3 bg-gray-50 rounded">
                <div class="text-xs text-gray-500">Phần trăm</div>
                <div class="text-xl font-bold">{{ $computedTotals['score'] ?? $attempt->score_percentage ?? 0 }}%</div>
            </div>
        </div>

        <h3 class="text-md font-semibold mb-2">Chi tiết câu hỏi</h3>
        <div class="space-y-3">
            @foreach($questions as $q)
                @php $ans = $answers->get($q->id); @endphp
                @php $part = $q->part ?? ($q->metadata['part'] ?? 1); @endphp
                <div class="p-3 border rounded">
                    @php $partToInclude = $part ?? ($quiz->part ?? 1); @endphp
                    @includeIf('student.listening.result_parts.part' . $partToInclude, ['question' => $q, 'answer' => $ans, 'quiz' => $quiz])
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            <a href="{{ route('student.listening.dashboard') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Quay lại bộ đề</a>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.getElementById('finalize-btn') && document.getElementById('finalize-btn').addEventListener('click', function(){
    if (!confirm('Bạn sẽ gửi kết quả lên server và hoàn tất bài làm. Tiếp tục?')) return;
    const btn = this; btn.disabled = true;
    const attemptId = {{ $attempt->id }};
    let answers = {};
    try { answers = JSON.parse(localStorage.getItem('attempt_answers_' + attemptId) || '{}'); } catch (e) { answers = window.attemptAnswers || {}; }

    fetch('{{ route('listening.practice.batchSubmit', $attempt->id) }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]') ? document.querySelector('meta[name=csrf-token]').getAttribute('content') : document.querySelector('input[name=_token]').value },
        body: JSON.stringify({ answers: answers, final: true })
    }).then(r => r.json()).then(resp => {
        btn.disabled = false;
        if (resp.success) {
            try { localStorage.removeItem('attempt_answers_' + attemptId); } catch (e) {}
            if (resp.redirect) window.location.href = resp.redirect; else location.reload();
        } else {
            alert(resp.message || 'Lỗi khi gửi kết quả');
        }
    }).catch(err => { console.error(err); btn.disabled = false; alert('Lỗi mạng'); });
});
</script>
@endpush
@endsection
