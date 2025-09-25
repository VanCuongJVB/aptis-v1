@extends('layouts.app')

@section('title', isset($question->id) ? 'Sửa câu hỏi Listening Part 3' : 'Thêm câu hỏi Listening Part 3')

@section('content')
<div class="container mx-auto py-6">
    <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
        <form method="POST" enctype="multipart/form-data" action="{{ isset($question->id) ? route('admin.questions.listening.part3.update', $question) : route('admin.questions.listening.part3.store') }}">
            @csrf
            @if(isset($question->id)) @method('PUT') @endif

            <div class="mb-4">
                <label class="block font-medium mb-1">Quiz</label>
                <select name="quiz_id" class="form-select w-full">
                    @foreach($quizzes as $quiz)
                        <option value="{{ $quiz->id }}" {{ old('quiz_id', $question->quiz_id) == $quiz->id ? 'selected' : '' }}>{{ $quiz->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Set</label>
                <select name="reading_set_id" class="form-select w-full">
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

            <div class="mb-4">
                <label class="block font-medium mb-1">Title</label>
                <input type="text" name="title" class="form-input w-full" value="{{ old('title', $question->metadata['title'] ?? '') }}">
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Description</label>
                <textarea name="description" class="form-textarea w-full">{{ old('description', $question->metadata['description'] ?? '') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Audio (upload mp3, wav)</label>
                <input type="file" name="audio_file" accept="audio/mpeg,audio/wav" class="form-input w-full mb-1">
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

                {{-- giữ lại path cũ nếu không upload mới --}}
                <input type="hidden" name="audio" value="{{ $audio }}">
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Items (4 ý kiến, mỗi dòng 1 item)</label>
                @php
                    $items = old('items', $question->metadata['items'] ?? []);
                @endphp
                @for($i=0; $i<4; $i++)
                    <input type="text" name="items[]" class="form-input w-full mb-1" value="{{ $items[$i] ?? '' }}" placeholder="Ý kiến {{ $i+1 }}">
                @endfor
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Options (3 lựa chọn, mỗi dòng 1 option)</label>
                @php
                    $defaultOptions = ['Man', 'Woman', 'Both'];
                    $options = old('options', $question->metadata['options'] ?? $defaultOptions);
                @endphp
                @for($i=0; $i<3; $i++)
                    <input type="text" name="options[]" class="form-input w-full mb-1" value="{{ $defaultOptions[$i] }}" placeholder="Option {{ chr(65+$i) }}" readonly>
                @endfor
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Đáp án cho từng item</label>
                @php
                    $answers = old('answers', $question->metadata['answers'] ?? []);
                @endphp
                @for($i=0; $i<4; $i++)
                    <div class="mb-2">
                        <label class="block text-sm font-medium mb-1">Ý kiến {{ $i+1 }}</label>
                        <select name="answers[]" class="form-select w-full">
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

            <div class="mt-6">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">
                    {{ isset($question->id) ? 'Cập nhật' : 'Tạo mới' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
