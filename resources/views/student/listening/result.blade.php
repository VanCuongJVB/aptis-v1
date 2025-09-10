@extends('layouts.app')

@section('title', 'Kết quả Listening')

@section('content')
<div class="container mx-auto py-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-lg font-semibold mb-2">Kết quả: {{ $quiz->title }}</h2>
        <div class="mb-4 text-sm text-gray-600">Bài làm bởi: bạn — Thời lượng: {{ $duration ?? 'N/A' }} phút</div>

        <div class="mb-4 grid grid-cols-3 gap-4">
            <div class="p-3 bg-gray-50 rounded">
                <div class="text-xs text-gray-500">Tổng câu</div>
                <div class="text-xl font-bold">{{ $attempt->total_questions ?? 0 }}</div>
            </div>
            <div class="p-3 bg-gray-50 rounded">
                <div class="text-xs text-gray-500">Đúng</div>
                <div class="text-xl font-bold">{{ $attempt->correct_answers ?? 0 }}</div>
            </div>
            <div class="p-3 bg-gray-50 rounded">
                <div class="text-xs text-gray-500">Phần trăm</div>
                <div class="text-xl font-bold">{{ $attempt->score_percentage ?? 0 }}%</div>
            </div>
        </div>

        <h3 class="text-md font-semibold mb-2">Chi tiết câu hỏi</h3>
        <div class="space-y-3">
            @foreach($questions as $q)
                @php $ans = $answers->get($q->id); @endphp
                <div class="p-3 border rounded">
                    <div class="text-sm text-gray-700">{!! $q->content ?? $q->title !!}</div>
                    <div class="mt-2 text-xs text-gray-500">Part {{ $q->part }}</div>
                    <div class="mt-2">
                        @if($ans)
                            <div class="text-sm">Trạng thái: @if($ans->is_correct) <span class="text-green-600">Đúng</span> @else <span class="text-red-600">Sai</span> @endif</div>
                            @if($ans->selected_option_id !== null)
                                <div class="text-sm text-gray-700">Lựa chọn: {{ $ans->selected_option_id }}</div>
                            @endif
                        @else
                            <div class="text-sm text-gray-500">Chưa trả lời</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            <a href="{{ route('student.dashboard') }}" class="btn">Thoát</a>
            <a href="{{ route('student.listening.dashboard') }}" class="btn">Listening</a>
            <button id="finalize-btn" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Hoàn thành & Gửi kết quả</button>
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
