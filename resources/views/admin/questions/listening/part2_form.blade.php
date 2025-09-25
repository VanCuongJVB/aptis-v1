@extends('layouts.app')

@section('title', isset($question->id) ? 'Sửa câu hỏi Listening Part 2' : 'Thêm câu hỏi Listening Part 2')

@section('content')
<div class="container mx-auto py-6">
    <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
        <form method="POST" enctype="multipart/form-data" action="{{ isset($question->id) ? route('admin.questions.listening.part2.update', $question) : route('admin.questions.listening.part2.store') }}">
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
                <label class="block font-medium mb-1">Stem</label>
                <input type="text" name="stem" class="form-input w-full" value="{{ old('stem', $question->stem) }}">
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Description</label>
                <textarea name="description" class="form-textarea w-full">{{ old('description', $question->metadata['description'] ?? '') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Options (6 đáp án, mỗi dòng 1 option)</label>
                @php
                    $options = old('options', $question->metadata['options'] ?? []);
                @endphp
                @for($i=0; $i<6; $i++)
                    <input type="text" name="options[]" class="form-input w-full mb-1" value="{{ $options[$i] ?? '' }}" placeholder="Option {{ chr(65+$i) }}">
                @endfor
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Đáp án cho từng speaker</label>
                @php
                    $answers = old('answers', $question->metadata['answers'] ?? []);
                @endphp
                @for($i=0; $i<4; $i++)
                    <div class="mb-2">
                        <label class="block text-sm font-medium mb-1">Speaker {{ chr(65+$i) }}</label>
                        <select name="answers[]" class="form-select w-full">
                            <option value="">-- Chọn đáp án --</option>
                            @for($j=0; $j<6; $j++)
                                <option value="{{ $j }}" {{ (isset($answers[$i]) && (string)$answers[$i] === (string)$j) ? 'selected' : '' }}>Option {{ chr(65+$j) }}</option>
                            @endfor
                        </select>
                    </div>
                @endfor
            </div>

            <div class="mb-4">
                <label class="block font-medium mb-1">Speakers (4 người, mỗi người gồm label, audio, description)</label>
                @php
                    $speakers = old('speakers', $question->metadata['speakers'] ?? [
                        ['label'=>'','audio'=>'','description'=>''],
                        ['label'=>'','audio'=>'','description'=>''],
                        ['label'=>'','audio'=>'','description'=>''],
                        ['label'=>'','audio'=>'','description'=>''],
                    ]);
                @endphp

                <div id="speakers-list">
                    @for($i=0; $i<4; $i++)
                        @php
                            $audioSrc = '';
                            $spAudio = $speakers[$i]['audio'] ?? '';
                            if ($spAudio) {
                                if (\Illuminate\Support\Str::startsWith($spAudio, ['http://','https://'])) {
                                    $audioSrc = $spAudio;
                                } elseif (\Illuminate\Support\Str::startsWith($spAudio, ['/'])) {
                                    $audioSrc = asset(ltrim($spAudio, '/'));
                                } else {
                                    $audioSrc = \Illuminate\Support\Facades\Storage::url($spAudio);
                                }
                            }
                        @endphp

                        <div class="border rounded p-3 mb-2">
                            <label>Label</label>
                            <input type="text" name="speakers[{{ $i }}][label]" class="form-input w-full mb-1" value="{{ $speakers[$i]['label'] ?? '' }}" placeholder="Speaker {{ chr(65+$i) }}">

                            <label>Audio (upload mp3, wav hoặc nhập đường dẫn)</label>
                            <input type="file" name="speakers[{{ $i }}][audio_file]" accept="audio/mp3,audio/wav" class="form-input w-full mb-1">

                            @if($audioSrc)
                                <audio controls class="w-full mb-1">
                                    <source src="{{ $audioSrc }}" type="audio/mpeg">
                                    Trình duyệt của bạn không hỗ trợ phát audio.
                                </audio>
                            @endif

                            {{-- giữ lại path cũ nếu không upload mới --}}
                            <input type="hidden" name="speakers[{{ $i }}][audio]" value="{{ $speakers[$i]['audio'] ?? '' }}">

                            <label>Description</label>
                            <textarea name="speakers[{{ $i }}][description]" class="form-textarea w-full">{{ $speakers[$i]['description'] ?? '' }}</textarea>
                        </div>
                    @endfor
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">
                    {{ isset($question->id) ? 'Cập nhật' : 'Tạo mới' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function addSpeaker() {
    var idx = document.querySelectorAll('#speakers-list > .border').length;
    var html = `<div class="border rounded p-3 mb-2">
        <label>Label</label>
        <input type="text" name="speakers[${idx}][label]" class="form-input w-full mb-1">
        <label>Audio (đường dẫn hoặc tên file)</label>
        <input type="text" name="speakers[${idx}][audio]" class="form-input w-full mb-1">
        <label>Description</label>
        <textarea name="speakers[${idx}][description]" class="form-textarea w-full"></textarea>
    </div>`;
    document.getElementById('speakers-list').insertAdjacentHTML('beforeend', html);
}
</script>
@endsection
