<div>
    @php
        $meta = $question->metadata ?? [];
        $sentences = $meta['sentences'] ?? [];
        $selected = $answer->metadata['selected'] ?? null;
    @endphp

    <p class="mb-2 font-medium">
        The sentences below are from a report. Put the sentences in the right order.
        The first sentence is done for you:
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Slots -->
        <div class="flex flex-col">
            <div class="mb-3 font-medium">
                Correct order (drag here according to position 1..{{ count($sentences) }})
            </div>
            <div id="slot-container" class="space-y-3 min-h-[320px] overflow-auto border p-3 rounded-lg">
                @for($i = 0; $i < count($sentences); $i++)
                    <div class="slot border-2 border-dashed rounded flex items-start px-3 py-2
                                    bg-white transition-colors duration-150 ease-in-out 
                                    hover:shadow-md hover:border-black 
                                    focus-within:ring-2 focus-within:ring-blue-200
                                    w-full max-w-full overflow-hidden" data-slot-index="{{ $i }}" tabindex="0"
                        aria-label="Slot {{ $i + 1 }}" style="min-height: 56px;">
                        <div class="flex-1 text-sm text-gray-400 break-words whitespace-normal leading-snug">&nbsp;</div>
                    </div>
                @endfor
            </div>
        </div>

        <!-- Pool -->
        <div class="flex flex-col">
            <div class="mb-3 font-medium">Các câu (kéo từ đây)</div>
            <div id="pool" class="space-y-3 min-h-[240px] overflow-auto border p-3 rounded-lg">
                @php
                    $pairs = [];
                    foreach ($sentences as $origIdx => $s) {
                        $label = is_array($s) && isset($s['text']) ? $s['text'] : $s;
                        $pairs[] = ['idx' => $origIdx, 'label' => $label];
                    }
                    shuffle($pairs);
                @endphp
                @foreach($pairs as $pair)
                    @php
                        $dataIndex = $pair['idx'];
                        $label = $pair['label'];
                    @endphp
                    <div class="draggable-item p-3 border rounded bg-gray-50 cursor-move flex items-start gap-2
                                    transition-colors duration-150 
                                    hover:shadow-md hover:border-black hover:bg-white 
                                    focus:outline-none focus:ring-2 focus:ring-blue-200
                                    w-full max-w-full" draggable="true" data-index="{{ $dataIndex }}" tabindex="0"
                        role="button">
                        <svg class="h-4 w-4 text-gray-500 shrink-0" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="3" y="3" width="6" height="6" rx="1" fill="currentColor"></rect>
                        </svg>
                        <div class="flex-1 text-sm break-words whitespace-normal leading-snug">
                            {{ e($label) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Hidden inputs -->
    <input type="hidden" name="part2_order" id="part2_order"
        value="{{ isset($selected['order']) ? e(json_encode($selected['order'])) : '' }}">
    <input type="hidden" name="part2_selected_texts" id="part2_selected_texts"
        value="{{ isset($selected['texts']) ? e(json_encode($selected['texts'])) : '' }}">
    <input type="hidden" name="part2_order_text" id="part2_order_text"
        value="{{ isset($selected['order']) ? implode(',', $selected['order']) : '' }}">

    @push('scripts')
        <script>
            (function () {
                const pool = document.getElementById('pool');
                const slots = document.getElementById('slot-container');
                const inputOrder = document.getElementById('part2_order');
                const inputTexts = document.getElementById('part2_selected_texts');
                const qid = @json($question->id);

                function addDrag(el) {
                    el.addEventListener('dragstart', e => {
                        e.dataTransfer.setData('text/plain', el.dataset.index);
                        el.classList.add('opacity-60');
                    });
                    el.addEventListener('dragend', () => el.classList.remove('opacity-60'));

                    // hover / focus visuals
                    el.addEventListener('mouseenter', () => el.classList.add('hovering'));
                    el.addEventListener('mouseleave', () => el.classList.remove('hovering'));
                    el.addEventListener('focus', () => el.classList.add('hovering'));
                    el.addEventListener('blur', () => el.classList.remove('hovering'));
                }

                function collect() {
                    const order = [];
                    const texts = [];
                    slots.querySelectorAll('.slot').forEach(slot => {
                        const item = slot.querySelector('.draggable-item');
                        if (item) {
                            order.push(item.dataset.index);
                            texts.push(item.innerText.trim());
                        } else {
                            order.push(null);
                        }
                    });
                    try { inputOrder.value = JSON.stringify(order); } catch (e) { inputOrder.value = ''; }
                    try { inputTexts.value = JSON.stringify(texts); } catch (e) { inputTexts.value = ''; }

                    try { if (window.readingPartHelper?.process) window.readingPartHelper.process(qid, { __part: 'part2', data: [texts.length ? texts : null, order] }); } catch (e) { }
                }

                function restore() {
                    try {
                        const savedOrder = inputOrder.value ? JSON.parse(inputOrder.value) : null;
                        if (!savedOrder) return;
                        savedOrder.forEach((idx, pos) => {
                            if (idx === null) return;
                            const src = pool.querySelector(`.draggable-item[data-index="${idx}"]`);
                            const slot = slots.querySelector(`.slot[data-slot-index="${pos}"]`);
                            if (src && slot) {
                                if (slot.firstChild) pool.appendChild(slot.firstChild);
                                slot.innerHTML = '';
                                slot.appendChild(src);
                            }
                        });
                        collect();
                    } catch (e) { console.warn('Restore failed', e); }
                }

                // slot drag highlight and drop
                slots.querySelectorAll('.slot').forEach(slot => {
                    slot.addEventListener('dragover', e => e.preventDefault());
                    slot.addEventListener('dragenter', () => slot.classList.add('ring-2', 'ring-blue-300'));
                    slot.addEventListener('dragleave', () => slot.classList.remove('ring-2', 'ring-blue-300'));
                    slot.addEventListener('drop', e => {
                        e.preventDefault();
                        slot.classList.remove('ring-2', 'ring-blue-300');
                        const idx = e.dataTransfer.getData('text/plain');
                        const src = document.querySelector(`.draggable-item[data-index="${idx}"]`);
                        if (src) {
                            if (slot.firstChild) pool.appendChild(slot.firstChild);
                            slot.innerHTML = '';
                            slot.appendChild(src);
                            collect();
                        }
                    });
                });

                // pool drop
                pool.addEventListener('dragover', e => e.preventDefault());
                pool.addEventListener('drop', e => {
                    e.preventDefault();
                    const idx = e.dataTransfer.getData('text/plain');
                    const src = document.querySelector(`.draggable-item[data-index="${idx}"]`);
                    if (src) pool.appendChild(src);
                    collect();
                });

                // init
                pool.querySelectorAll('.draggable-item').forEach(addDrag);
                restore();
            })();
        </script>

        @includeWhen(true, 'student.reading.parts._check_helper')
    @endpush
</div>

<div class="inline-feedback mt-3 text-sm text-gray-700" data-qid-feedback="{{ $question->id }}"></div>