<div>
    @php
        $meta = $question->metadata ?? [];
        $sentences = $meta['sentences'] ?? [];
        $selected = $answer->metadata['selected'] ?? null;
    @endphp

    <p class="mb-2 font-medium">Sắp xếp các câu sau theo đúng thứ tự (kéo & thả):</p>

    <div class="grid grid-cols-2 gap-4">
        <div class="flex flex-col">
            <div class="mb-2 font-medium">Đúng theo thứ tự (kéo vào đây theo vị trí 1..5)</div>
            <div id="slot-container" class="space-y-2 min-h-[220px] overflow-auto">
                @for($i=0;$i<count($sentences);$i++)
                    <div class="slot h-12 border border-dashed rounded flex items-center px-3 bg-white" data-slot-index="{{ $i }}">
                        <span class="text-sm text-gray-400">Kéo câu vào ô {{ $i+1 }}</span>
                    </div>
                @endfor
            </div>
        </div>

        <div class="flex flex-col">
            <div class="mb-2 font-medium">Các câu (kéo từ đây)</div>
            <div id="pool" class="space-y-2 min-h-[220px] overflow-auto">
                @foreach($sentences as $idx => $s)
                    <div class="draggable-item p-2 border rounded bg-gray-50 cursor-move flex items-center gap-2" draggable="true" data-index="{{ $idx }}">
                        <svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="3" y="3" width="6" height="6" rx="1" fill="currentColor"></rect></svg>
                        <div class="flex-1 text-sm">{{ e($s) }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-3 flex items-center gap-2">
    <button type="button" id="shuffle-btn" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Xáo câu</button>
    <button type="button" id="clear-btn" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Xóa</button>
        <p class="text-sm text-gray-600 ml-4">Khi sắp xếp xong, nhấn Nộp bài để lưu.</p>
    </div>

    {{-- Hidden/backup inputs kept for compatibility with existing evaluateClient on the page --}}
    <input type="hidden" name="part2_order" id="part2_order" value="{{ isset($selected['order']) ? e(json_encode($selected['order'])) : '' }}">
    <input type="hidden" name="part2_selected_texts" id="part2_selected_texts" value="{{ isset($selected['texts']) ? e(json_encode($selected['texts'])) : '' }}">
    <input type="hidden" name="part2_order_text" id="part2_order_text" value="{{ isset($selected['order']) ? implode(',', $selected['order']) : '' }}">

    @push('scripts')
    <script>
    (function(){
        const pool = document.getElementById('pool');
        const slots = document.getElementById('slot-container');
        const shuffleBtn = document.getElementById('shuffle-btn');
        const clearBtn = document.getElementById('clear-btn');
        const inputOrderText = document.getElementById('part2_order_text');
        const inputOrder = document.getElementById('part2_order');
        const inputTexts = document.getElementById('part2_selected_texts');

        let draggingSourceSlot = null;
        function makeDraggable(el){
            el.addEventListener('dragstart', function(e){
                e.dataTransfer.setData('text/plain', el.dataset.index);
                // capture source slot index if any
                const parentSlot = el.closest('.slot');
                draggingSourceSlot = parentSlot ? parentSlot.dataset.slotIndex : null;
                e.dataTransfer.effectAllowed = 'move';
                el.classList.add('opacity-60');
            });
            el.addEventListener('dragend', function(){ el.classList.remove('opacity-60'); draggingSourceSlot = null; });
        }

        function clearSlots(){
            Array.from(slots.querySelectorAll('.slot')).forEach(s => {
                s.innerHTML = '<span class="text-sm text-gray-400">Kéo câu vào ô ' + (parseInt(s.dataset.slotIndex)+1) + '</span>';
                s.dataset.filled = '';
            });
            // restore pool items (move back any items)
            rebuildPool();
            writeInputs();
        }

        function createDraggableNode(idx, text){
            const d = document.createElement('div');
            d.className = 'draggable-item p-2 border rounded bg-gray-50 cursor-move flex items-center gap-2';
            d.draggable = true;
            d.dataset.index = idx;
            d.innerHTML = '<svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="3" y="3" width="6" height="6" rx="1" fill="currentColor"></rect></svg>' + '<div class="flex-1 text-sm">' + text + '</div>';
            makeDraggable(d);
            return d;
        }

        function rebuildPool(){
            // collect used indices
            const used = new Set();
            Array.from(slots.querySelectorAll('.slot')).forEach(s => {
                const di = s.querySelector('.draggable-item');
                if (di) used.add(String(di.dataset.index));
            });
            // find all unique items by scanning initial pool + slots
            const seen = new Map();
            Array.from(document.querySelectorAll('.draggable-item')).forEach(el => {
                seen.set(String(el.dataset.index), el.textContent.trim());
            });
            pool.innerHTML = '';
            seen.forEach((text, idx) => {
                if (!used.has(String(idx))) {
                    const node = createDraggableNode(idx, text);
                    pool.appendChild(node);
                }
            });
        }

        function writeInputs(){
            const order = [];
            const texts = [];
            Array.from(slots.querySelectorAll('.slot')).forEach(s => {
                const di = s.querySelector('.draggable-item');
                if (di) {
                    order.push(Number(di.dataset.index));
                    texts.push(di.textContent.trim());
                }
            });
            inputOrderText.value = order.join(',');
            inputOrder.value = JSON.stringify(order);
            inputTexts.value = JSON.stringify(texts);
        }

        // Setup slot drop handlers
        Array.from(slots.querySelectorAll('.slot')).forEach(slot => {
            slot.addEventListener('dragover', function(e){ e.preventDefault(); e.dataTransfer.dropEffect = 'move'; slot.classList.add('bg-gray-50'); });
            slot.addEventListener('dragleave', function(){ slot.classList.remove('bg-gray-50'); });
            slot.addEventListener('drop', function(e){
                e.preventDefault(); slot.classList.remove('bg-gray-50');
                const idx = e.dataTransfer.getData('text/plain');
                if (!idx) return;
                // find source element (in pool or other slot)
                const src = document.querySelector('.draggable-item[data-index="'+idx+'"]');
                if (!src) return;
                // if slot already has an item, move that item back to pool
                const exist = slot.querySelector('.draggable-item');
                if (exist) {
                    // move exist back to pool (move the DOM node)
                    pool.appendChild(exist);
                }
                // move src into slot (remove from its parent)
                slot.innerHTML = '';
                slot.appendChild(src);
                src.classList.remove('opacity-60');
                writeInputs();
                rebuildPool();
            });
        });

        // allow dragging within pool (reorder not necessary) and from slots back to pool
        pool.addEventListener('dragover', function(e){ e.preventDefault(); e.dataTransfer.dropEffect = 'move'; });
        pool.addEventListener('drop', function(e){
            e.preventDefault();
            const idx = e.dataTransfer.getData('text/plain');
            if (!idx) return;
            const src = document.querySelector('.draggable-item[data-index="'+idx+'"]');
            if (!src) return;
            // Append to pool (this moves the node)
            pool.appendChild(src);
            // If it came from a slot, restore that slot's placeholder
            if (draggingSourceSlot !== null) {
                const origSlot = slots.querySelector('.slot[data-slot-index="'+draggingSourceSlot+'"]');
                if (origSlot && !origSlot.querySelector('.draggable-item')) {
                    origSlot.innerHTML = '<span class="text-sm text-gray-400">Kéo câu vào ô ' + (parseInt(origSlot.dataset.slotIndex)+1) + '</span>';
                }
            }
            writeInputs();
            rebuildPool();
        });

        // Initialize draggable handlers for initial pool items
        Array.from(document.querySelectorAll('#pool .draggable-item')).forEach(makeDraggable);

        shuffleBtn.addEventListener('click', function(){
            // shuffle pool children
            const items = Array.from(document.querySelectorAll('#pool .draggable-item'));
            for (let i = items.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                pool.insertBefore(items[j], items[i]);
            }
        });

        clearBtn.addEventListener('click', clearSlots);

        // If server provided selected, populate slots
        (function populateFromSelected(){
            try{
                const selJson = inputTexts.value ? JSON.parse(inputTexts.value) : null;
                const selIdxJson = inputOrder.value ? JSON.parse(inputOrder.value) : null;
                if (Array.isArray(selIdxJson) && selIdxJson.length > 0) {
                    // move items to slots in order
                    selIdxJson.forEach((si, i) => {
                        const src = document.querySelector('.draggable-item[data-index="'+si+'"]');
                        const slot = slots.querySelector('.slot[data-slot-index="'+i+'"]');
                        if (src && slot) {
                            slot.innerHTML = '';
                            slot.appendChild(src);
                        }
                    });
                    rebuildPool();
                    writeInputs();
                }
            }catch(e){/* ignore */}
        })();

    })();
    </script>
    @endpush
</div>
