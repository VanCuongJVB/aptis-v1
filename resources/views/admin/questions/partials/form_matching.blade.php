@php
  // Lấy meta hiện có (khi edit) hoặc từ old() sau khi validate fail
  $meta = old('meta_json')
      ? (json_decode(old('meta_json'), true) ?? [])
      : ($question->meta ?? []);

  // Chuyển sources từ dạng [title, body, title, body, ...] -> [{title, body}, ...] cho dễ edit
  $pairs = [];
  $src = $meta['sources'] ?? [];
  for ($i = 0; $i < count($src); $i += 2) {
      $pairs[] = ['title' => $src[$i] ?? '', 'body' => $src[$i + 1] ?? ''];
  }
  if (empty($pairs)) {
      $pairs = [
          ['title' => 'Person A', 'body' => ''],
          ['title' => 'Person B', 'body' => ''],
      ];
  }

  $items  = $meta['items']  ?? [''];
  $answer = $meta['answer'] ?? []; // ["1"=>"A", ...]
@endphp

<div class="space-y-4" id="matching-root">
  {{-- Hidden sẽ chứa JSON nộp lên server --}}
  <input type="hidden" name="meta_json" id="meta_json_input" value="{{ e(json_encode([
      'sources' => $meta['sources'] ?? [],
      'items'   => $items,
      'answer'  => $answer,
  ], JSON_UNESCAPED_UNICODE)) }}">

  {{-- SOURCES --}}
  <div class="border rounded p-3">
    <div class="flex items-center justify-between mb-2">
      <div class="font-medium">Nguồn (Person A–…)</div>
      <div class="space-x-2">
        <button type="button" class="px-2 py-1 text-sm rounded bg-emerald-600 text-white" id="btnAddSource">+ Thêm nguồn</button>
        <button type="button" class="px-2 py-1 text-sm rounded bg-slate-200" id="btnRelabel">Cập nhật nhãn</button>
      </div>
    </div>

    <div id="sourcesWrap" class="space-y-2"></div>
    <p class="text-xs text-slate-500">* Nhãn A/B/C/D tự động theo thứ tự nguồn.</p>
  </div>

  {{-- ITEMS --}}
  <div class="border rounded p-3">
    <div class="flex items-center justify-between mb-2">
      <div class="font-medium">Câu hỏi (Items)</div>
      <button type="button" class="px-2 py-1 text-sm rounded bg-emerald-600 text-white" id="btnAddItem">+ Thêm item</button>
    </div>
    <div id="itemsWrap" class="space-y-2"></div>
  </div>

  {{-- ANSWER KEY --}}
  <div class="border rounded p-3">
    <div class="font-medium mb-2">Đáp án</div>
    <div id="answersWrap" class="space-y-2"></div>
  </div>

  {{-- Live preview --}}
  <div class="border rounded p-3 bg-slate-50">
    <div class="font-semibold mb-2">Preview</div>
    <div id="livePreview" class="text-sm"></div>
  </div>
</div>

<script>
(function(){
  // ======= STATE =======
  const state = {
    sources: @json($pairs),      // [{title, body}, ...] để edit
    items:   @json($items),      // ["Who suggests...", ...]
    answer:  @json($answer),     // {"1":"A", ...}
    labels:  [],                 // ["A","B","C",...]
  };

  const elMetaInput   = document.getElementById('meta_json_input');
  const elSourcesWrap = document.getElementById('sourcesWrap');
  const elItemsWrap   = document.getElementById('itemsWrap');
  const elAnsWrap     = document.getElementById('answersWrap');
  const elPreview     = document.getElementById('livePreview');

  // ======= HELPERS =======
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, m => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[m]));

  function relabel() {
    state.labels = Array.from({length: state.sources.length}, (_, i) => String.fromCharCode(65 + i));
  }

  function sourcesToSchemaArray() {
    // Trả về dạng ["title","body","title","body",...]
    const arr = [];
    state.sources.forEach(p => { arr.push(p.title ?? ''); arr.push(p.body ?? ''); });
    return arr;
  }

  function syncMetaInputAndPreview() {
    // Build meta JSON theo schema hiện có
    const meta = {
      sources: sourcesToSchemaArray(),
      items: state.items,
      answer: state.answer
    };
    elMetaInput.value = JSON.stringify(meta);

    // Preview
    let html = '';
    state.sources.forEach((p, i) => {
      const L = state.labels[i] ?? '?';
      html += `
        <div class="border rounded p-2 mb-2">
          <div class="font-semibold">Person ${L}</div>
          ${p.title ? `<div class="text-xs text-slate-500 mb-1">${esc(p.title)}</div>` : ''}
          <div class="whitespace-pre-wrap">${esc(p.body)}</div>
        </div>
      `;
    });
    html += `<hr class="my-3">`;
    state.items.forEach((t, i) => {
      const ai = state.answer[String(i+1)] || '';
      html += `
        <div class="mb-1">
          <span class="font-semibold">${i+1}.</span> ${esc(t)}
          <span class="ml-2 inline-block text-xs px-2 py-0.5 border rounded">${ai || '—'}</span>
        </div>
      `;
    });
    elPreview.innerHTML = html;
  }

  // ======= RENDERERS =======
  function renderSources() {
    elSourcesWrap.innerHTML = '';
    state.sources.forEach((p, idx) => {
      const L = state.labels[idx] ?? '?';
      const row = document.createElement('div');
      row.className = 'border rounded p-2';
      row.innerHTML = `
        <div class="flex items-center justify-between">
          <div class="font-semibold">Person ${L}</div>
          <button type="button" class="text-rose-600 text-sm" data-action="removeSource" data-idx="${idx}">Xoá</button>
        </div>
        <div class="grid md:grid-cols-2 gap-2 mt-2">
          <label class="block">
            <span class="text-xs text-slate-500">Tiêu đề</span>
            <input type="text" class="w-full border rounded px-2 py-1" data-bind="sourceTitle" data-idx="${idx}" value="${esc(p.title)}">
          </label>
          <label class="block md:col-span-2">
            <span class="text-xs text-slate-500">Nội dung</span>
            <textarea rows="3" class="w-full border rounded px-2 py-1" data-bind="sourceBody" data-idx="${idx}">${esc(p.body)}</textarea>
          </label>
        </div>
      `;
      elSourcesWrap.appendChild(row);
    });
  }

  function renderItems() {
    elItemsWrap.innerHTML = '';
    state.items.forEach((t, idx) => {
      const row = document.createElement('div');
      row.className = 'flex gap-2 items-start';
      row.innerHTML = `
        <div class="w-8 mt-2 font-semibold">${idx+1}.</div>
        <input type="text" class="grow border rounded px-2 py-1" data-bind="itemText" data-idx="${idx}" value="${esc(t)}">
        <button type="button" class="text-rose-600 text-sm" data-action="removeItem" data-idx="${idx}">Xoá</button>
      `;
      elItemsWrap.appendChild(row);
    });
  }

  function renderAnswers() {
    elAnsWrap.innerHTML = '';
    state.items.forEach((_, idx) => {
      const sel = document.createElement('div');
      sel.className = 'flex items-center gap-2';
      const k = String(idx+1);
      const current = state.answer[k] || '';
      const options = ['<option value="">— chọn —</option>']
        .concat(state.labels.map(L => `<option value="${L}" ${current===L?'selected':''}>${L}</option>`));

      sel.innerHTML = `
        <div class="w-8 font-semibold">${idx+1}.</div>
        <select class="border rounded px-2 py-1" data-bind="answerAt" data-key="${k}">
          ${options.join('')}
        </select>
      `;
      elAnsWrap.appendChild(sel);
    });
  }

  function rerenderAll() {
    relabel();
    renderSources();
    renderItems();
    renderAnswers();
    syncMetaInputAndPreview();
  }

  // ======= EVENTS (delegation) =======
  document.getElementById('btnAddSource').addEventListener('click', () => {
    const nextLabel = String.fromCharCode(65 + state.sources.length); // A,B,C,...
    state.sources.push({ title: 'Person ' + nextLabel, body: '' });
    rerenderAll();
  });

  document.getElementById('btnRelabel').addEventListener('click', () => {
    rerenderAll();
  });

  elSourcesWrap.addEventListener('input', (e) => {
    const target = e.target;
    if (target.dataset.bind === 'sourceTitle') {
      const i = parseInt(target.dataset.idx, 10);
      state.sources[i].title = target.value;
      syncMetaInputAndPreview();
    } else if (target.dataset.bind === 'sourceBody') {
      const i = parseInt(target.dataset.idx, 10);
      state.sources[i].body = target.value;
      syncMetaInputAndPreview();
    }
  });

  elSourcesWrap.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="removeSource"]');
    if (!btn) return;
    const i = parseInt(btn.dataset.idx, 10);
    state.sources.splice(i, 1);
    // Xóa nguồn -> vẫn giữ answer như cũ; admin nên tự rà lại
    rerenderAll();
  });

  document.getElementById('btnAddItem').addEventListener('click', () => {
    state.items.push('');
    // Dịch lại keys answer theo số item mới
    const newAns = {};
    state.items.forEach((_, j) => { newAns[String(j+1)] = state.answer[String(j+1)] || ''; });
    state.answer = newAns;
    rerenderAll();
  });

  elItemsWrap.addEventListener('input', (e) => {
    const target = e.target;
    if (target.dataset.bind === 'itemText') {
      const i = parseInt(target.dataset.idx, 10);
      state.items[i] = target.value;
      syncMetaInputAndPreview();
    }
  });

  elItemsWrap.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="removeItem"]');
    if (!btn) return;
    const i = parseInt(btn.dataset.idx, 10);
    state.items.splice(i, 1);
    // Cập nhật lại answer keys
    const newAns = {};
    state.items.forEach((_, j) => { newAns[String(j+1)] = state.answer[String(j+1)] || ''; });
    state.answer = newAns;
    rerenderAll();
  });

  elAnsWrap.addEventListener('change', (e) => {
    const target = e.target;
    if (target.dataset.bind === 'answerAt') {
      const key = target.dataset.key; // "1","2",...
      state.answer[key] = target.value;
      syncMetaInputAndPreview();
    }
  });

  // ======= INIT =======
  rerenderAll();
})();
</script>
