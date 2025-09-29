@extends('layouts.app')

@section('title', isset($question->id) ? 'Sửa câu hỏi Listening Part 3' : 'Thêm câu hỏi Listening Part 3')

@section('content')

<div class="container mx-auto px-2 py-8">
    <div class="max-w-7xl mx-auto bg-white rounded-3xl shadow-xl border border-slate-200 p-6 md:p-10">
        <h1 class="text-3xl font-extrabold mb-6 flex items-center gap-3 text-blue-800 tracking-tight">
            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            {{ isset($question->id) ? 'Sửa' : 'Tạo' }} <span class="text-blue-600">Listening Part 3</span> <span class="text-slate-500 text-lg font-normal">— Matching Opinions</span>
        </h1>

        <p class="text-base text-slate-600 mb-5">Form quản trị <span class="font-semibold text-blue-700">Aptis Listening Part 3</span> (Matching Opinions). Các trường có dấu <span class="text-red-500">*</span> là bắt buộc.</p>

        <form method="POST" enctype="multipart/form-data" action="{{ isset($question->id) ? route('admin.questions.listening.part3.update', $question) : route('admin.questions.listening.part3.store') }}">
            @csrf
            @if(isset($question->id)) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-base font-semibold text-slate-700 mb-1">Quiz <span class="text-red-500">*</span></label>
                    <select name="quiz_id" class="w-full rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-base text-blue-800 font-bold shadow-sm">
                        @foreach($quizzes as $quiz)
                            <option value="{{ $quiz->id }}" {{ old('quiz_id', $question->quiz_id) == $quiz->id ? 'selected' : '' }}>{{ $quiz->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-base font-semibold text-slate-700 mb-1">Set <span class="text-red-500">*</span></label>
                    <select name="reading_set_id" class="w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-base text-emerald-800 font-bold shadow-sm">
                        <option value="">-- Không chọn --</option>
                        @foreach($sets as $set)
                            <option value="{{ $set->id }}"
                                @if(request('reading_set_id'))
                                    {{ request('reading_set_id') == $set->id ? 'selected' : '' }}
                                @else
                                    {{ old('reading_set_id', $question->reading_set_id) == $set->id ? 'selected' : '' }}
                                @endif
                            >{{ $set->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-base font-semibold text-slate-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition" value="{{ old('title', $question->metadata['title'] ?? '') }}">
            </div>

            <div class="mb-6">
                <label class="block text-base font-semibold text-slate-700 mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition">{{ old('description', $question->metadata['description'] ?? '') }}</textarea>
            </div>

            <div class="mb-6">
                <label class="block text-base font-semibold text-slate-700 mb-1">Audio (upload mp3, wav)</label>
                <input type="file" name="audio_file" accept="audio/mpeg,audio/wav" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition mb-1">
                @php
                    $audio = old('audio', $question->metadata['audio'] ?? '');
                    $audioSrc = '';
                    if ($audio) {
                        if (\Illuminate\Support\Str::startsWith($audio, ['http://', 'https://'])) {
                            $audioSrc = $audio;
                        } elseif (\Illuminate\Support\Str::startsWith($audio, ['/'])) {
                            $audioSrc = asset(ltrim($audio, '/'));
                        } else {
                            $audioSrc = \Illuminate\Support\Facades\Storage::url($audio);
                        }
                    }
                @endphp
                @if($audioSrc)
                    <audio controls class="w-full mb-1">
                        <source src="{{ $audioSrc }}" type="audio/mpeg">
                        Trình duyệt của bạn không hỗ trợ phát audio.
                    </audio>
                @endif
                <input type="hidden" name="audio" value="{{ $audio }}">
            </div>

            <div class="mb-10">
                <label class="block text-base font-semibold text-slate-700 mb-2">Items (4 ý kiến, mỗi dòng 1 item) <span class="text-red-500">*</span></label>
                @php
                    $items = old('items', $question->metadata['items'] ?? []);
                @endphp
                <div class="grid grid-cols-1 gap-2">
                    @for($i=0; $i<4; $i++)
                        <input type="text" name="items[]" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow mb-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition" value="{{ $items[$i] ?? '' }}" placeholder="Ý kiến {{ $i+1 }}">
                    @endfor
                </div>
            </div>

            <div class="mb-10">
                <label class="block text-base font-semibold text-slate-700 mb-2">Options (3 lựa chọn, mỗi dòng 1 option) <span class="text-red-500">*</span></label>
                @php
                    $defaultOptions = ['Man', 'Woman', 'Both'];
                    $options = old('options', $question->metadata['options'] ?? $defaultOptions);
                @endphp
                <div class="grid grid-cols-1 gap-2">
                    @for($i=0; $i<3; $i++)
                        <input type="text" name="options[]" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow mb-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition" value="{{ $defaultOptions[$i] }}" placeholder="Option {{ chr(65+$i) }}" readonly>
                    @endfor
                </div>
            </div>

            <div class="mb-10">
                <label class="block text-base font-semibold text-slate-700 mb-2">Đáp án cho từng item <span class="text-red-500">*</span></label>
                @php
                    $answers = old('answers', $question->metadata['answers'] ?? []);
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @for($i=0; $i<4; $i++)
                        <div>
                            <label class="block text-sm font-medium mb-1">Ý kiến {{ $i+1 }}</label>
                            <select name="answers[]" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition">
                                <option value="">-- Chọn đáp án --</option>
                                @for($j=0; $j<3; $j++)
                                    <option value="{{ $j }}" {{ (isset($answers[$i]) && (string)$answers[$i] === (string)$j) ? 'selected' : '' }}>
                                        {{ $options[$j] ?? 'Option '.chr(65+$j) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    @endfor
                </div>
            </div>

            <div class="flex justify-end mt-8 gap-4">
                <a href="{{ route('admin.quizzes.questions', ['part' => 3]) }}"
                    class="inline-flex items-center px-8 py-3 bg-white border border-slate-300 rounded-2xl font-semibold text-base text-blue-700 shadow hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500/60 transition">
                    <span class="px-2">Quay lại</span>
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-8 py-3 bg-blue-600 border border-transparent rounded-2xl font-semibold text-base text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/60 transition">
                    <span class="px-2">{{ isset($question->id) ? 'Cập nhật' : 'Tạo mới' }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
