@extends('layouts.app')

@section('title', isset($question->id) ? 'Sửa Listening Part 2' : 'Thêm Listening Part 2')

@section('content')

<div class="container mx-auto px-2 py-8">
    <div class="max-w-7xl mx-auto bg-white rounded-3xl shadow-xl border border-slate-200 p-6 md:p-10">
        <h1 class="text-3xl font-extrabold mb-6 flex items-center gap-3 text-blue-800 tracking-tight">
            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            {{ isset($question->id) ? 'Sửa' : 'Tạo' }} <span class="text-blue-600">Listening Part 2</span> <span class="text-slate-500 text-lg font-normal">— Matching</span>
        </h1>

        <p class="text-base text-slate-600 mb-5">Form quản trị <span class="font-semibold text-blue-700">Aptis Listening Part 2</span> (Matching). Các trường có dấu <span class="text-red-500">*</span> là bắt buộc.</p>

        @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-100 text-emerald-800 rounded-lg border border-emerald-200">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-3 bg-rose-100 text-rose-800 rounded-lg border border-rose-200">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" enctype="multipart/form-data" action="{{ isset($question->id) ? route('admin.questions.listening.part2.update', $question) : route('admin.questions.listening.part2.store') }}">
            @csrf
            @if(isset($question->id)) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-base font-semibold text-slate-700 mb-1">Quiz</label>
                    <select name="quiz_id" class="w-full rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-base text-blue-800 font-bold shadow-sm">
                        @foreach($quizzes as $quiz)
                            <option value="{{ $quiz->id }}" {{ old('quiz_id', $question->quiz_id) == $quiz->id ? 'selected' : '' }}>{{ $quiz->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-base font-semibold text-slate-700 mb-1">Set</label>
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
                <label class="block text-base font-semibold text-slate-700 mb-1">Stem <span class="text-red-500">*</span></label>
                <input type="text" name="stem" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition" value="{{ old('stem', $question->stem) }}">
                @error('stem')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-6">
                <label class="block text-base font-semibold text-slate-700 mb-1">Description</label>
                <textarea name="description" rows="6" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition">{{ old('description', $question->metadata['description'] ?? '') }}</textarea>
            </div>

            <div class="mb-10">
                <label class="block text-base font-semibold text-slate-700 mb-2">Options (6 đáp án, mỗi dòng 1 option) <span class="text-red-500">*</span></label>
                @php
                    $options = old('options', $question->metadata['options'] ?? []);
                @endphp
                <div class="grid grid-cols-1 gap-2">
                    @for($i=0; $i<6; $i++)
                        <input type="text" name="options[]" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow mb-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition" value="{{ $options[$i] ?? '' }}" placeholder="Option {{ chr(65+$i) }}">
                        @error('options.' . $i)<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                    @endfor
                </div>
                <div class="text-xs text-gray-500 mt-1">Nhập 6 đáp án, mỗi dòng 1 đáp án.</div>
            </div>

            <div class="mb-10">
                <label class="block text-base font-semibold text-slate-700 mb-2">Đáp án cho từng speaker <span class="text-red-500">*</span></label>
                @php
                    $answers = old('answers', $question->metadata['answers'] ?? []);
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @for($i=0; $i<4; $i++)
                        <div>
                            <label class="block text-sm font-medium mb-1">Speaker {{ chr(65+$i) }}</label>
                            <select name="answers[]" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition">
                                <option value="">-- Chọn đáp án --</option>
                                @for($j=0; $j<6; $j++)
                                    <option value="{{ $j }}" {{ (isset($answers[$i]) && (string)$answers[$i] === (string)$j) ? 'selected' : '' }}>Option {{ chr(65+$j) }}</option>
                                @endfor
                            </select>
                            @error('answers.' . $i)<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                        </div>
                    @endfor
                </div>
                <div class="text-xs text-gray-500 mt-1">Chọn đáp án đúng cho từng speaker.</div>
            </div>

            <div class="mb-10">
                <label class="block text-base font-semibold text-slate-700 mb-2">Speakers (4 người, mỗi người gồm label, audio, description) <span class="text-red-500">*</span></label>
                @php
                    $speakers = old('speakers', $question->metadata['speakers'] ?? [
                        ['label'=>'','audio'=>'','description'=>''],
                        ['label'=>'','audio'=>'','description'=>''],
                        ['label'=>'','audio'=>'','description'=>''],
                        ['label'=>'','audio'=>'','description'=>''],
                    ]);
                @endphp
                <div id="speakers-list" class="grid grid-cols-1 gap-4">
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
                        <div class="border border-slate-200 rounded-2xl p-4 bg-slate-50">
                            <div class="mb-2">
                                <label class="block text-base font-semibold mb-1">Label</label>
                                <input type="text" name="speakers[{{ $i }}][label]" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-base text-slate-800 shadow mb-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition" value="{{ $speakers[$i]['label'] ?? '' }}" placeholder="Speaker {{ chr(65+$i) }}">
                            </div>
                            <div class="mb-2">
                                <label class="block text-base font-semibold mb-1">Audio (upload mp3, wav hoặc nhập đường dẫn)</label>
                                <input type="file" name="speakers[{{ $i }}][audio_file]" accept="audio/mp3,audio/wav" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-base text-slate-800 shadow mb-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition">
                                @if($audioSrc)
                                    <audio controls class="w-full mb-1" crossorigin="anonymous" playsinline>
                                        <source src="{{ $audioSrc }}" type="audio/mpeg">
                                        Trình duyệt của bạn không hỗ trợ phát audio.
                                    </audio>
                                @endif
                                <input type="hidden" name="speakers[{{ $i }}][audio]" value="{{ $speakers[$i]['audio'] ?? '' }}">
                            </div>
                            <div>
                                <label class="block text-base font-semibold mb-1">Description</label>
                                <textarea name="speakers[{{ $i }}][description]" rows="6" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition">{{ $speakers[$i]['description'] ?? '' }}</textarea>
                            </div>
                        </div>
                    @endfor
                </div>
                <div class="text-xs text-gray-500 mt-1">Nhập thông tin cho từng speaker.</div>
            </div>

            <div class="flex justify-end mt-8 gap-4">
                <a href="{{ route('admin.quizzes.questions', ['part' => 2]) }}"
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
