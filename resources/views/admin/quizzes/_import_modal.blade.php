<div id="importModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <!-- modal panel: constrain height and allow internal scrolling -->
        <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full overflow-y-auto" style="max-height: 90vh">
            <div class="p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-medium">Nhập câu hỏi</h3>
                        <p class="text-sm text-gray-600">
                            Tải lên một tệp chứa dữ liệu bài thi, bộ câu hỏi và câu hỏi. Đây chỉ là giao diện mẫu.
                        </p>
                    </div>
                    <button onclick="closeImportModal()" class="text-gray-500">Đóng</button>
                </div>

                <form id="importForm" method="POST" action="{{ route('admin.quizzes.import') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="mt-4">
                        <label class="block font-medium">Tệp dữ liệu</label>
                        <input id="importFile" name="file" type="file" accept="application/json" class="mt-2" />
                        <div class="mt-2 text-sm text-gray-600">
                            Cần mẫu tệp? <a href="{{ route('admin.quizzes.import.template') }}" download
                                class="text-indigo-600 underline">Tải dữ liệu hiện tại (JSON)</a>
                        </div>
                    </div>

                    <div id="importPreview" class="mt-4 bg-gray-50 p-4 rounded hidden">
                        <h4 class="font-semibold">Xem trước</h4>
                        <p id="previewSummary" class="text-sm text-gray-700 mt-2"></p>
                        <pre id="previewJson"
                            class="text-xs bg-white p-2 rounded border mt-2 max-h-[60vh] overflow-auto"></pre>
                    </div>

                    <div id="serverSummary" class="mt-4 bg-yellow-50 p-4 rounded hidden">
                        <h4 class="font-semibold">Kiểm tra từ máy chủ</h4>
                        <p id="serverSummaryText" class="text-sm text-gray-700 mt-2"></p>
                        <ul id="serverProblems" class="list-disc pl-5 text-sm text-red-700 mt-2"></ul>
                    </div>

                    <div id="importProgress" class="mt-4 hidden">
                        <div class="text-sm text-gray-700 mb-2">Đang tải lên: <span id="progressPercent">0%</span></div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progressBar" class="bg-indigo-600 h-2 rounded-full" style="width:0%"></div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="inline-flex items-center">
                            <input id="confirmErase" type="checkbox" class="mr-2" />
                            <span class="text-sm text-gray-700"><span class="text-red-500">* </span>Tôi hiểu rằng thao tác này sẽ xóa toàn bộ bài thi, bộ
                                câu hỏi và câu hỏi hiện có</span>
                        </label>
                    </div>

                    <div class="mt-4 sticky bottom-0 bg-white p-4 border-t flex justify-end space-x-2 z-10">
                        <button type="button" onclick="closeImportModal()" class="px-4 py-2 border rounded">Hủy</button>
                        <button id="serverDryRunBtn" type="button"
                            class="px-4 py-2 bg-yellow-400 text-black rounded">Chạy thử (máy chủ)</button>
                        <button id="parseBtn" type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded">Xem
                            trước</button>
                        <button id="uploadBtn" type="button" class="px-4 py-2 bg-indigo-600 text-white rounded hidden"
                            disabled>Bắt đầu nhập</button>
                    </div>
                </form>

                <script>
                    (function () {
                        const fileInput = document.getElementById('importFile');
                        const parseBtn = document.getElementById('parseBtn');
                        const uploadBtn = document.getElementById('uploadBtn');
                        const serverDryRunBtn = document.getElementById('serverDryRunBtn');
                        const confirmErase = document.getElementById('confirmErase');
                        const serverSummary = document.getElementById('serverSummary');
                        const serverSummaryText = document.getElementById('serverSummaryText');
                        const serverProblems = document.getElementById('serverProblems');
                        const preview = document.getElementById('importPreview');
                        const previewJson = document.getElementById('previewJson');
                        const previewSummary = document.getElementById('previewSummary');
                        const progress = document.getElementById('importProgress');
                        const progressBar = document.getElementById('progressBar');
                        const progressPercent = document.getElementById('progressPercent');
                        let parsed = null;

                        parseBtn.addEventListener('click', function () {
                            if (!fileInput.files || !fileInput.files[0]) { alert('Choose a JSON file first'); return; }
                            const f = fileInput.files[0];
                            const reader = new FileReader();
                            reader.onload = function (e) {
                                try {
                                    parsed = JSON.parse(e.target.result);
                                } catch (err) {
                                    alert('Invalid JSON: ' + err.message);
                                    parsed = null;
                                    return;
                                }

                                // build summary
                                const quizzes = Array.isArray(parsed.quizzes) ? parsed.quizzes : [];
                                let setsCount = 0, questionsCount = 0;
                                quizzes.forEach(q => { if (Array.isArray(q.sets)) { setsCount += q.sets.length; q.sets.forEach(s => { if (Array.isArray(s.questions)) questionsCount += s.questions.length; }); } });

                                previewSummary.textContent = `${quizzes.length} quizzes, ${setsCount} sets, ${questionsCount} questions`;
                                previewJson.textContent = JSON.stringify(parsed, null, 2);
                                preview.classList.remove('hidden');
                                uploadBtn.classList.remove('hidden');
                                // scroll preview into view
                                preview.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            };
                            reader.readAsText(f);
                        });

                        // Enable Start Import only when confirmation is checked and server dry-run either passed or user accepts
                        confirmErase.addEventListener('change', function () {
                            uploadBtn.disabled = !confirmErase.checked;
                        });

                        serverDryRunBtn.addEventListener('click', async function () {
                            if (!fileInput.files || !fileInput.files[0]) { alert('Choose a JSON file first'); return; }
                            const f = fileInput.files[0];
                            const formData = new FormData();
                            formData.append('file', f);

                            serverSummary.classList.add('hidden');
                            serverProblems.innerHTML = '';
                            serverSummaryText.textContent = 'Validating...';
                            serverSummary.classList.remove('hidden');
                            serverDryRunBtn.disabled = true;

                            try {
                                const token = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : null;
                                const resp = await fetch('{{ route('admin.quizzes.import.dryrun') }}', {
                                    method: 'POST',
                                    headers: token ? { 'X-CSRF-TOKEN': token } : {},
                                    body: formData
                                });
                                const data = await resp.json();
                                serverDryRunBtn.disabled = false;
                                if (!data.success) {
                                    serverSummaryText.textContent = 'Validation failed';
                                    (data.errors || data.message || []).forEach(err => {
                                        const li = document.createElement('li'); li.textContent = err; serverProblems.appendChild(li);
                                    });
                                    uploadBtn.disabled = true;
                                    return;
                                }

                                const s = data.summary || {};
                                serverSummaryText.textContent = `Quizzes: ${s.quizzes || 0}, Sets: ${s.sets || 0}, Questions: ${s.questions || 0}`;
                                if (s.problems && s.problems.length) {
                                    s.problems.forEach(p => { const li = document.createElement('li'); li.textContent = p; serverProblems.appendChild(li); });
                                    uploadBtn.disabled = true;
                                } else {
                                    const ok = document.createElement('div'); ok.textContent = 'No structural problems found.'; ok.className = 'text-sm text-green-700 mt-2'; serverSummary.appendChild(ok);
                                    // only allow start if confirmation checkbox is checked
                                    uploadBtn.disabled = !confirmErase.checked;
                                }

                            } catch (err) {
                                serverDryRunBtn.disabled = false;
                                serverSummaryText.textContent = 'Validation error';
                                const li = document.createElement('li'); li.textContent = err.message || String(err); serverProblems.appendChild(li);
                                uploadBtn.disabled = true;
                            }
                        });

                        uploadBtn.addEventListener('click', function () {
                            if (!fileInput.files || !fileInput.files[0]) { alert('Choose a JSON file first'); return; }
                            const f = fileInput.files[0];
                            const form = document.getElementById('importForm');
                            const url = form.action;
                            const token = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : null;

                            const fd = new FormData();
                            fd.append('file', f);

                            progress.classList.remove('hidden');
                            uploadBtn.disabled = true;
                            parseBtn.disabled = true;

                            const xhr = new XMLHttpRequest();
                            xhr.open('POST', url, true);
                            if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token);

                            xhr.upload.addEventListener('progress', function (e) {
                                if (e.lengthComputable) {
                                    const pct = Math.round((e.loaded / e.total) * 100);
                                    progressBar.style.width = pct + '%';
                                    progressPercent.textContent = pct + '%';
                                }
                            });

                            xhr.onload = function () {
                                uploadBtn.disabled = false;
                                parseBtn.disabled = false;
                                serverDryRunBtn.disabled = false;
                                if (xhr.status >= 200 && xhr.status < 300) {
                                    // success: redirect or show message
                                    try {
                                        // Laravel redirects usually come back as full HTML; just reload to pick up flash messages
                                        window.location.reload();
                                    } catch (e) {
                                        alert('Import completed. Refresh the page.');
                                    }
                                } else {
                                    let msg = 'Import failed';
                                    try { const r = JSON.parse(xhr.responseText); if (r.message) msg = r.message; } catch (e) { }
                                    alert(msg);
                                }
                            };

                            xhr.onerror = function () {
                                uploadBtn.disabled = false;
                                parseBtn.disabled = false;
                                alert('Network error during upload');
                            };

                            xhr.send(fd);
                        });
                    })();
                </script>
            </div>
        </div>
    </div>
</div>