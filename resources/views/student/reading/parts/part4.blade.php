<div>
    {{-- Part 4: long text with options pool --}}
    @php
        $meta = $question->metadata ?? [];
        $paragraphs = is_array($meta['paragraphs'] ?? null) ? $meta['paragraphs'] : [];
        $options = is_array($meta['options'] ?? null) ? $meta['options'] : [];
        $selected = is_array($answer->metadata['selected'] ?? null) ? $answer->metadata['selected'] : [];
    @endphp

    @php
        $pairs = [];
        foreach ($options as $optIndex => $opt) {
            $pairs[] = ['idx' => $optIndex, 'label' => $opt];
        }
        shuffle($pairs);
    @endphp

    @foreach($paragraphs as $i => $p)
        <div class="p-3 border rounded mb-3">
            <div class="mb-2 flex items-start gap-3">
                <div class="text-sm font-medium">{{ $i + 1 }}.</div>
                <div class="flex-1 relative">
                    {{-- Hidden input that will be submitted in the form --}}
                    <input type="hidden" name="part4_choice[{{ $i }}]" value="{{ isset($selected[$i]) ? e($selected[$i]) : '' }}" class="part4-hidden-{{ $i }}">

                    {{-- Visible custom dropdown button --}}
                    @php
                        $currentLabel = '- Select -';
                        if (isset($selected[$i])) {
                            foreach ($pairs as $p) {
                                if ((string)$p['idx'] === (string)$selected[$i]) { $currentLabel = $p['label']; break; }
                            }
                        }
                    @endphp

                    <button type="button" class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm text-left flex items-center justify-between custom-select-button" data-select-index="{{ $i }}">
                        <span class="custom-select-label">{{ e($currentLabel) }}</span>
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    {{-- Dropdown panel (hidden by default) --}}
                    <div class="custom-select-panel hidden absolute left-0 right-0 mt-2 bg-white border border-gray-200 rounded shadow-lg z-50 max-h-56 overflow-auto text-sm leading-normal">
                        <div class="px-3 py-1 hover:bg-blue-600 hover:text-white cursor-pointer select-option text-sm" data-value="">- Select -</div>
                        @foreach($pairs as $pair)
                            <div class="px-3 py-1 hover:bg-blue-600 hover:text-white cursor-pointer select-option whitespace-normal break-words text-sm" data-value="{{ e($pair['idx']) }}">{!! $pair['label'] !!}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-3 text-sm text-gray-700">{!! nl2br($p) !!}</div>
        </div>
    @endforeach

    @includeWhen(true, 'student.reading.parts._check_helper')
</div>


<div class="inline-feedback mt-3 text-sm text-gray-700" data-qid-feedback="{{ $question->id }}"></div>

@once
@push('scripts')
<script>
document.addEventListener('click', function(e){
    // close any open custom select panel when clicking outside
    const openPanels = document.querySelectorAll('.custom-select-panel:not(.hidden)');
    openPanels.forEach(panel => {
        if (!panel.contains(e.target) && !panel.previousElementSibling?.contains(e.target)) {
            panel.classList.add('hidden');
        }
    });
});

function initCustomPart4Selects() {
    try {
        const buttons = document.querySelectorAll('.custom-select-button');
        const panels = document.querySelectorAll('.custom-select-panel');
        const opts = document.querySelectorAll('.custom-select-panel .select-option');

        // mark buttons as initialized (delegated handlers handle clicks)
        document.querySelectorAll('.custom-select-button').forEach(function(btn){
            btn.dataset.part4Init = '1';
        });

    // initial hidden inputs (no debug logging)
    document.querySelectorAll('input[type="hidden"][name^="part4_choice"]').forEach(function(h){ /* init */ });

        // mark options as initialized (delegated handlers handle clicks and hover)
        document.querySelectorAll('.custom-select-panel .select-option').forEach(function(opt){
            opt.dataset.part4Init = '1';
        });
    } catch (e) { /* init error ignored */ }
}

// initialize on DOMContentLoaded and when SPA container is replaced
document.addEventListener('DOMContentLoaded', initCustomPart4Selects);
document.addEventListener('aptis:container:replace', function(ev){
    // small delay to allow DOM replacement to settle
    setTimeout(function(){ initCustomPart4Selects(); }, 120);
});
// also try immediate init in case DOMContentLoaded already fired
initCustomPart4Selects();

// Delegated handlers so newly-inserted DOM nodes (SPA navigation) work without per-node init
document.addEventListener('click', function(e){
    try {
        const btn = e.target.closest && e.target.closest('.custom-select-button');
        if (btn) {
            e.preventDefault();
            const panel = btn.parentElement && btn.parentElement.querySelector('.custom-select-panel');
            if (!panel) { return; }
            panel.classList.toggle('hidden');
            return;
        }

        const opt = e.target.closest && e.target.closest('.select-option');
        if (opt) {
            const container = opt.closest && opt.closest('.relative');
            if (!container) { return; }
            const input = container.querySelector && container.querySelector('input[type="hidden"]');
            const btn2 = container.querySelector && container.querySelector('.custom-select-button');
            const panel2 = container.querySelector && container.querySelector('.custom-select-panel');
            const value = opt.getAttribute('data-value');
            const label = opt.textContent.trim();
            if (input) input.value = value;
            if (btn2) btn2.querySelector('.custom-select-label').textContent = label;
            if (panel2) panel2.classList.add('hidden');
            return;
        }
    } catch (e) { /* delegated click error ignored */ }
});

// Delegated hover (mouseover/mouseout) to apply inline hover styles
document.addEventListener('mouseover', function(e){
    const opt = e.target.closest && e.target.closest('.select-option');
    if (opt) { try { opt.style.backgroundColor = '#2563eb'; opt.style.color = '#ffffff'; } catch(e) {} }
});
document.addEventListener('mouseout', function(e){
    const opt = e.target.closest && e.target.closest('.select-option');
    if (opt) { try { opt.style.backgroundColor = ''; opt.style.color = ''; } catch(e) {} }
});
</script>
@endpush
@endonce

