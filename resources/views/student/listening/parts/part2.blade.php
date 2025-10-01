<div class="w-full max-w-3xl mx-auto p-4 question-block mb-6" 
     data-qid="{{ $question->id }}" 
     data-part="{{ $question->part ?? 2 }}"
     data-metadata='@json(array_merge($question->metadata, ["optionMapping" => array_keys($question->metadata['options'] ?? [])]))'>
    
    {{-- Audio area --}}
    @php
        use Illuminate\Support\Str;
        use Illuminate\Support\Facades\Storage;
        $speakers = $question->metadata['speakers'] ?? [];
        $hasSpeakerAudio = false;
        foreach($speakers as $sp) {
            if (!empty($sp['audio'])) { $hasSpeakerAudio = true; break; }
        }
    @endphp
    @if($hasSpeakerAudio && count($speakers) > 0)
        <div class="mb-2">
            <button type="button" id="play-all-{{ $question->id }}" class="mb-3 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none text-sm">
                Phát tất cả
            </button>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($speakers as $spIdx => $sp)
                    @php
                        $spAudioPath = $sp['audio'] ?? null;
                        $spAudioUrl = null;
                        if ($spAudioPath) {
                            if (Str::startsWith($spAudioPath, ['http://','https://'])) {
                                $spAudioUrl = $spAudioPath;
                            } elseif (Str::startsWith($spAudioPath, ['/'])) {
                                $spAudioUrl = asset(ltrim($spAudioPath, '/'));
                            } else {
                                $spAudioUrl = Storage::url($spAudioPath);
                            }
                        }
                    @endphp
                    <div class="border rounded p-2 flex flex-col">
                        <div class="font-medium text-sm mb-1 flex items-center">
                            {{ $sp['label'] ?? 'Speaker '.chr(65+$spIdx) }}
                        </div>
                        @if($spAudioUrl)
                            <audio controls preload="none" class="w-full mb-1 playall-audio" id="audio-{{ $question->id }}-{{ $spIdx }}">
                                <source src="{{ $spAudioUrl }}" type="audio/mpeg">
                                Trình duyệt của bạn không hỗ trợ phát audio.
                            </audio>
                        @else
                            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                                <span>🎧</span> Không có file âm thanh cho speaker này.
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @elseif(!empty($audioUrl))
        <div class="mb-4">
            <audio controls preload="none" class="w-full">
                <source src="{{ $audioUrl }}" type="audio/mpeg">
                Trình duyệt của bạn không hỗ trợ audio.
            </audio>
        </div>
    @endif
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Câu hỏi {{ $question->order_no }}</h2>
        @if(!empty($question->stem))
            <p class="text-gray-700 mt-1">{{ $question->stem }}</p>
        @endif
    </div>

    <form class="space-y-3">
        @php
            $options = $question->metadata['options'] ?? [];
        @endphp
        @foreach($speakers as $idx => $speaker)
            <div class="p-3 border rounded-md">
                <div class="text-sm font-medium mb-2 flex items-center">
                    {{ $speaker['label'] }}
                </div>
                <select class="w-full border rounded p-2 speaker-select part2-select" data-index="{{ $idx }}">
                    <option value="">- Chọn câu mô tả -</option>
                    @foreach(array_keys($options) as $newIdx)
                        <option value="{{ $newIdx }}">{{ e($options[$newIdx]) }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
    </form>
@push('scripts')
<script>
function bindAllPlayAllBtnsPart2() {
    const playAllBtns = document.querySelectorAll('[id^="play-all-"]');
    playAllBtns.forEach(function(playAllBtn) {
        // Remove old event listeners by cloning
        const newBtn = playAllBtn.cloneNode(true);
        playAllBtn.parentNode.replaceChild(newBtn, playAllBtn);
        // Find all audios in the same question block
        const qBlock = newBtn.closest('.question-block');
        const audios = qBlock ? Array.from(qBlock.querySelectorAll('.playall-audio')) : [];
        if (!audios.length) return;
        let isPlayingAll = false, userStopped = false;
        function stopAll(){
            audios.forEach(a => { a.pause(); a.currentTime=0; a.onended=null; });
            isPlayingAll = false;
            userStopped = false;
            newBtn.textContent = 'Phát tất cả';
        }
        function playNext(idx){
            if(!isPlayingAll || idx >= audios.length){ stopAll(); return; }
            const audio = audios[idx];
            audios.forEach((a,i)=>{ if(i!==idx){ a.pause(); a.currentTime=0;} });
            audio.currentTime=0; audio.play();
            audio.onended=function(){ if(isPlayingAll && !userStopped) playNext(idx+1); else stopAll(); }
        }
        newBtn.addEventListener('click', function(){
            if(isPlayingAll){ userStopped=true; stopAll(); }
            else{ isPlayingAll=true; userStopped=false; newBtn.textContent='Dừng phát tất cả'; playNext(0); }
        });
    });
}

document.addEventListener('DOMContentLoaded', function(){
    bindAllPlayAllBtnsPart2();
});
window.addEventListener('aptis:container:replace', function(ev){
    setTimeout(function(){
        bindAllPlayAllBtnsPart2();
    }, 100);
});
</script>
@endpush

    <div id="feedback-{{ $question->id }}" class="mt-4 hidden"></div>
    <div class="inline-feedback mt-3 text-sm text-gray-700" data-qid-feedback="{{ $question->id }}"></div>
</div>
