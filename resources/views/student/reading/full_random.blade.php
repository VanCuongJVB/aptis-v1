@extends('layouts.app')
@section('content')
    <h1 class="text-xl font-bold mb-4 px-4">Đề Reading Full Random</h1>
    <div id="full-random-questions" class="px-4" style="padding-bottom: 390px;">
        @php
            $allQuestions = collect([$part1, $part2, $part3, $part4])->flatten(1);
            $idx = 0;
        @endphp
        @foreach($allQuestions as $question)
            <div class="question-slide" data-index="{{ $idx++ }}" style="display: none;">
                <div class="mb-2 text-xs text-gray-500">Part {{ $question->part ?? ($question->metadata['part'] ?? '') }}</div>
                @include('student.reading.parts.part' . ($question->part ?? ($question->metadata['part'] ?? '')), ['question' => $question])
            </div>
        @endforeach
    </div>
    <footer id="full-random-footer" class="w-full bg-white border-t shadow-sm fixed bottom-0 left-0 right-0 z-50">
        <div class="mx-auto px-4 py-3 flex justify-between items-center gap-2">
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
                        </div>
                    </div>
                </div>
            </div>
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
                // Nếu là part2 thì khởi tạo lại drag & drop
                const currentSlide = slides[idx];
                if (currentSlide) {
                    const partLabel = currentSlide.querySelector('.mb-2.text-xs.text-gray-500');
                    if (partLabel && partLabel.textContent.includes('Part 2')) {
                        // Tìm script khởi tạo drag & drop trong part2
                        if (window.initPart2DragDrop) {
                            console.log('[full_random] Init drag & drop part2', currentSlide);
                            setTimeout(function() { window.initPart2DragDrop(currentSlide); }, 10);
                        } else {
                            console.warn('[full_random] window.initPart2DragDrop not found');
                        }
                    }
                }
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
                    const results = slides.map(slide => {
                        const qblock = slide.querySelector('.question-block');
                        const qid = qblock?.dataset.qid;
                        let meta = {};
                        let part = null;
                        if (qblock && qblock.dataset.metadata) {
                            try {
                                meta = JSON.parse(qblock.dataset.metadata);
                            } catch (e) {
                                meta = {};
                            }
                        }
                        if (qblock && qblock.dataset.part) {
                            part = parseInt(qblock.dataset.part);
                        }
                        let correctAnswer = null;
                        let correct = false; // Boolean value for validation
                        if (meta) {
                            if (part == 1) {
                                correctAnswer = meta.correct_answers ?? meta.answers ?? null;
                            } else if (part == 2) {
                                correctAnswer = meta.correct_order ?? meta.order ?? meta.answers ?? null;
                            } else if (part == 3) {
                                correctAnswer = meta.answers ?? meta.correct ?? null;
                            } else if (part == 4) {
                                correctAnswer = meta.answers ?? meta.correct ?? null;
                            }
                        }
                        let userAnswer = null;
                        if (part == 2) {
                            // lấy từ input[name=part2_selected_texts] (json)
                            const txtInput = qblock?.querySelector('input[name="part2_selected_texts"]');
                            if (txtInput && txtInput.value) {
                                try {
                                    userAnswer = JSON.parse(txtInput.value);
                                } catch (e) {
                                    userAnswer = txtInput.value;
                                }
                            }
                        } else if (part == 4) {
                            // lấy từ các input[name^=part4_choice]
                            const hiddenInputs = qblock?.querySelectorAll('input[type=hidden][name^="part4_choice"]') || [];
                            userAnswer = Array.from(hiddenInputs).map(inp => inp.value);
                        } else {
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
                            const allSelects = qblock?.querySelectorAll('select') || [];
                            if (allSelects.length > 1) {
                                userAnswer = Array.from(allSelects).map(s => s.value);
                            }
                        }
                        
                        // Enhanced handling for specific part structures based on the provided metadata
                        try {
                            // Default is false
                            correct = false;
                            
                            // Part-specific handling for the metadata structures you provided
                            if (part === 1) {
                                // Part 1 - Fill in the blanks with choices
                                if (meta && meta.correct_answers && userAnswer !== null) {
                                    const userStr = String(userAnswer).trim().toLowerCase();
                                    
                                    if (Array.isArray(meta.correct_answers)) {
                                        // Direct array of correct answers
                                        correct = meta.correct_answers.some(ans => 
                                            String(ans).trim().toLowerCase() === userStr);
                                    } else if (typeof meta.correct_answers === 'string') {
                                        // Single correct answer as string
                                        correct = meta.correct_answers.trim().toLowerCase() === userStr;
                                    }
                                }
                            } else if (part === 2) {
                                // Part 2 - Sentence ordering
                                if (meta && meta.correct_order && Array.isArray(userAnswer)) {
                                    // For order-based exercises, check if the user's order matches the correct order
                                    if (userAnswer.length === meta.correct_order.length) {
                                        // Check each position
                                        correct = userAnswer.every((val, idx) => {
                                            return String(val).trim() === String(meta.correct_order[idx]).trim();
                                        });
                                    }
                                }
                            } else if (part === 3) {
                                // Part 3 - Category matching (answers is an object with arrays)
                                if (meta && meta.answers && typeof meta.answers === 'object') {
                                    // If userAnswer is a mapping of categories to options
                                    if (typeof userAnswer === 'object' && !Array.isArray(userAnswer)) {
                                        const categories = Object.keys(meta.answers);
                                        const userCategories = Object.keys(userAnswer);
                                        
                                        // Check if all required categories are present
                                        if (categories.every(cat => userCategories.includes(cat))) {
                                            // Check each category's answers
                                            const results = categories.map(cat => {
                                                const correctItems = meta.answers[cat] || [];
                                                const userItems = userAnswer[cat] || [];
                                                
                                                // Make sure all items are correctly assigned
                                                return correctItems.every(item => userItems.includes(Number(item)));
                                            });
                                            
                                            correct = results.every(result => result === true);
                                        }
                                    }
                                }
                            } else if (part === 4) {
                                // Part 4 - Paragraph ordering/matching
                                if (meta && (meta.correct || meta.answers) && Array.isArray(userAnswer)) {
                                    const correctSeq = meta.correct || meta.answers;
                                    
                                    // Check if arrays match in length and content
                                    if (Array.isArray(correctSeq) && userAnswer.length === correctSeq.length) {
                                        correct = userAnswer.every((val, idx) => {
                                            return String(val).trim() === String(correctSeq[idx]).trim();
                                        });
                                    }
                                }
                            }
                            
                            // Fallback if specific part handling didn't work - generic comparison
                            if (correct === false && userAnswer !== null && correctAnswer !== null) {
                                if (Array.isArray(userAnswer) && Array.isArray(correctAnswer)) {
                                    // Array comparison
                                    if (userAnswer.length === correctAnswer.length) {
                                        correct = userAnswer.every((val, idx) => 
                                            String(val).trim() === String(correctAnswer[idx]).trim());
                                    }
                                } else if (typeof userAnswer === 'object' && typeof correctAnswer === 'object') {
                                    // Object comparison
                                    const userKeys = Object.keys(userAnswer);
                                    const correctKeys = Object.keys(correctAnswer);
                                    
                                    if (userKeys.length === correctKeys.length) {
                                        correct = userKeys.every(key => {
                                            const uVal = userAnswer[key];
                                            const cVal = correctAnswer[key];
                                            
                                            if (Array.isArray(uVal) && Array.isArray(cVal)) {
                                                return uVal.length === cVal.length && 
                                                    uVal.every((v, i) => String(v).trim() === String(cVal[i]).trim());
                                            } else {
                                                return String(uVal).trim() === String(cVal).trim();
                                            }
                                        });
                                    }
                                } else {
                                    // Simple value comparison
                                    correct = String(userAnswer).trim() === String(correctAnswer).trim();
                                }
                            }
                            
                            // IMPORTANT: Ensure correct is boolean (true/false)
                            correct = correct === true;
                        } catch (e) {
                            console.error('Error determining correctness:', e);
                            correct = false;
                        }
                        
                        return { qid, part, correct, userAnswer, correctAnswer };
                    });
                    // POST results to server and redirect to server-rendered result page
                    (async function() {
                        try {
                            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                            const res = await fetch('{{ route('reading.full_random_result.store') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': token || ''
                                },
                                body: JSON.stringify({ answers: results })
                            });
                            const json = await res.json();
                            const nextBtn = document.getElementById('full-random-next');
                            // disable to prevent double submits
                            if (nextBtn) { nextBtn.disabled = true; nextBtn.dataset.orig = nextBtn.innerHTML; nextBtn.innerHTML = 'Đang lưu...'; }
                            if (res.ok && json.success) {
                                // Redirect to server result page (attempt id)
                                if (json.redirect) {
                                    window.location.href = json.redirect;
                                } else if (json.attempt) {
                                    window.location.href = '/reading/full-random/result/' + json.attempt;
                                } else {
                                    window.location.href = '/reading/full-random/result';
                                }
                            } else {
                                    console.error('Failed to save results', json);
                                    alert('Không thể lưu kết quả. Vui lòng thử lại.');
                                    if (nextBtn) { nextBtn.disabled = false; nextBtn.innerHTML = nextBtn.dataset.orig || 'Hoàn thành'; }
                            }
                        } catch (e) {
                            console.error('Save results error', e);
                            alert('Có lỗi khi lưu kết quả. Vui lòng thử lại.');
                            if (nextBtn) { nextBtn.disabled = false; nextBtn.innerHTML = nextBtn.dataset.orig || 'Hoàn thành'; }
                        }
                    })();
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
