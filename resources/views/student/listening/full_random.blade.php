@extends('layouts.app')
@section('content')
    <h1 class="text-xl font-bold mb-4 px-4">Đề Listening Full Random</h1>
    <div id="full-random-questions" class="px-4" style="padding-bottom: 80px;">
        @php
            $allQuestions = collect([$part1, $part2, $part3, $part4])->flatten(1);
            $idx = 0;
        @endphp
        @foreach($allQuestions as $question)
            @php
                $audioPath = data_get($question->metadata, 'audio') ?? $question->audio_path ?? $question->audio;
                $audioUrl = null;
                if ($audioPath) {
                    if (\Illuminate\Support\Str::startsWith($audioPath, ['http://', 'https://'])) {
                        $audioUrl = $audioPath;
                    } elseif (\Illuminate\Support\Str::startsWith($audioPath, ['/'])) {
                        $audioUrl = asset(ltrim($audioPath, '/'));
                    } else {
                        $audioUrl = \Illuminate\Support\Facades\Storage::url($audioPath);
                    }
                }
            @endphp
            <div class="question-slide" data-index="{{ $idx++ }}" style="display: none;">
                <div class="mb-2 text-xs text-gray-500">Part {{ $question->part ?? ($question->metadata['part'] ?? '') }}</div>
                @include('student.listening.parts.part' . ($question->part ?? ($question->metadata['part'] ?? '')), ['question' => $question, 'audioUrl' => $audioUrl])
            </div>
        @endforeach
    </div>
    <footer id="full-random-footer" class="w-full bg-white border-t shadow-sm fixed bottom-0 left-0 right-0 z-50">
        <div class="mx-auto px-4 py-3 flex justify-between items-center gap-2">
            <!-- Left buttons -->
            <div class="flex items-center space-x-2">
                <button type="button" id="full-random-prev"
                    class="btn-square btn-base-large rounded bg-gray-200 text-gray-700 hover:bg-gray-300" title="Câu trước">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <div class="ml-3 text-sm text-gray-600 flex items-center gap-2">
                    <span id="full-random-status"></span>
                    <div class="relative ml-2">
                        <button id="full-random-jump-btn" type="button" class="flex items-center px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-700 shadow-sm focus:ring-2 focus:ring-blue-400 focus:outline-none hover:border-blue-400 transition duration-200">
                            <span id="full-random-jump-label">Chọn câu</span>
                            <svg class="ml-2 h-4 w-4 text-gray-400 transform rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="full-random-jump-list" class="hidden absolute z-50 mb-2 bottom-full w-48 max-h-72 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg">
                            <!-- List items will be injected by JS -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- Right buttons -->
            <div class="ml-auto flex items-center space-x-3">
                <button type="button" title="Thoát" class="btn-square btn-base rounded text-gray-700"
                    onclick="window.location.href='/student/dashboard'">
                    <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" />
                    </svg>
                    <span class="sr-only">Thoát</span>
                </button>
                <button type="button" class="btn-base-large btn-primary rounded flex items-center" id="full-random-next">
                    <span id="full-random-next-label" class="mr-2">Next</span>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true">
                        <path d="M5 12h14M13 5l7 7-7 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </footer>
    <script>
        (function () {
            const slides = Array.from(document.querySelectorAll('.question-slide'));
            let current = 0;
            function show(idx) {
                slides.forEach((el, i) => { el.style.display = (i === idx) ? '' : 'none'; });
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
            function updateBtn() {
                const btnNext = document.getElementById('full-random-next');
                const btnPrev = document.getElementById('full-random-prev');
                const lbl = document.getElementById('full-random-next-label');
                const status = document.getElementById('full-random-status');
                const jumpLabel = document.getElementById('full-random-jump-label');
                const jumpList = document.getElementById('full-random-jump-list');
                btnPrev.disabled = (current === 0);
                if (current >= slides.length - 1) {
                    lbl.textContent = 'Hoàn thành';
                } else {
                    lbl.textContent = 'Next';
                }
                status.textContent = `Câu ${current + 1} / ${slides.length}`;
                // Fill jump list (always clear and rebuild to avoid duplicate listeners)
                if (jumpList) {
                    jumpList.innerHTML = '';
                    for (let i = 0; i < slides.length; ++i) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = `w-full text-left px-4 py-2 text-sm border-b border-gray-100 hover:bg-blue-50 focus:bg-blue-100 ${i === current ? 'bg-purple-100 font-bold' : ''}`;
                        btn.textContent = `Câu ${i + 1}`;
                        btn.dataset.idx = i;
                        btn.onclick = function() {
                            current = i;
                            show(current);
                            updateBtn();
                            jumpList.classList.add('hidden');
                        };
                        jumpList.appendChild(btn);
                    }
                }
                if (jumpLabel) jumpLabel.textContent = `Câu ${current + 1}`;
            }
            // Dropdown-like jump
            const jumpBtn = document.getElementById('full-random-jump-btn');
            const jumpList = document.getElementById('full-random-jump-list');
            if (jumpBtn && jumpList) {
                jumpBtn.addEventListener('click', function(e) {
                    jumpList.classList.toggle('hidden');
                });
                document.addEventListener('click', function(e) {
                    if (!jumpBtn.contains(e.target) && !jumpList.contains(e.target)) {
                        jumpList.classList.add('hidden');
                    }
                });
            }
            document.getElementById('full-random-next').addEventListener('click', function () {
                if (current < slides.length - 1) {
                    current++;
                    show(current);
                    updateBtn();
                } else {
                    // Log ra data khi hoàn thành
                    const results = slides.map(slide => {
                        const qblock = slide.querySelector('.question-block');
                        const qid = qblock?.dataset.qid;
                        let meta = {};
                        let part = null;
                        if (qblock && qblock.dataset.metadata) {
                            try {
                                meta = JSON.parse(qblock.dataset.metadata);
                                part = meta.part || null;
                            } catch (e) {
                                meta = {};
                                part = null;
                            }
                        }
                        const correct = meta.correct_index ?? meta.correct ?? meta.answers ?? null;
                        // Lấy đáp án user chọn (radio, select, textarea)
                        let userAnswer = null;
                        const checked = qblock?.querySelector('input[type=radio]:checked');
                        if (checked) userAnswer = checked.value;
                        else {
                            const sel = qblock?.querySelector('select');
                            if (sel) userAnswer = sel.value;
                            else {
                                const ta = qblock?.querySelector('textarea');
                                if (ta) userAnswer = ta.value;
                            }
                        }
                        // Nếu có nhiều select (part3, part4), lấy hết
                        const allSelects = qblock?.querySelectorAll('select') || [];
                        if (allSelects.length > 1) {
                            userAnswer = Array.from(allSelects).map(s => s.value);
                        }
                        return { qid, part, correct, userAnswer };
                    });
                    console.log('[full_random] Hoàn thành:', results);
                    alert('Đã hoàn thành! (Xem log trên console)');
                }
            });
            document.getElementById('full-random-prev').addEventListener('click', function () {
                if (current > 0) {
                    current--;
                    show(current);
                    updateBtn();
                }
            });
            show(current);
            updateBtn();
        })();
    </script>
@endsection