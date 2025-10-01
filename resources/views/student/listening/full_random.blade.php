@extends('layouts.app')

@section('content')
    <h1 class="text-xl font-bold mb-4 px-4">Đề Listening Full Random</h1>

    <div id="full-random-questions" class="px-4" style="padding-bottom: 80px;">
        @php
            // Gom tất cả part lại thành 1 collection
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
                <div class="mb-2 text-xs text-gray-500">
                    Part {{ $question->part ?? ($question->metadata['part'] ?? '') }}
                </div>
                @include(
                    'student.listening.parts.part' . ($question->part ?? ($question->metadata['part'] ?? '')),
                    ['question' => $question, 'audioUrl' => $audioUrl]
                )
            </div>
        @endforeach
    </div>

    <footer id="full-random-footer"
        class="w-full bg-white border-t shadow-sm fixed bottom-0 left-0 right-0 z-50">
        <div class="mx-auto px-4 py-3 flex justify-between items-center gap-2">
            <!-- Left buttons -->
            <div class="flex items-center space-x-2">
                <button type="button" id="full-random-prev"
                    class="btn-square btn-base-large rounded bg-gray-200 text-gray-700 hover:bg-gray-300"
                    title="Câu trước">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <div class="ml-3 text-sm text-gray-600 flex items-center gap-2">
                    <span id="full-random-status"></span>
                    <div class="relative ml-2">
                        <button id="full-random-jump-btn" type="button"
                            class="flex items-center px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-700 shadow-sm focus:ring-2 focus:ring-blue-400 focus:outline-none hover:border-blue-400 transition duration-200">
                            <span id="full-random-jump-label">Chọn câu</span>
                            <svg class="ml-2 h-4 w-4 text-gray-400 transform rotate-180" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="full-random-jump-list"
                            class="hidden absolute z-50 mb-2 bottom-full w-48 max-h-72 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg">
                            <!-- List items sẽ được inject bằng JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right buttons -->
            <div class="ml-auto flex items-center space-x-3">
                <button type="button" title="Thoát" class="btn-square btn-base rounded text-gray-700"
                    onclick="window.location.href='/student/dashboard'">
                    <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" />
                    </svg>
                    <span class="sr-only">Thoát</span>
                </button>
                <button type="button" class="btn-base-large btn-primary rounded flex items-center"
                    id="full-random-next">
                    <span id="full-random-next-label" class="mr-2">Next</span>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M5 12h14M13 5l7 7-7 7" stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </footer>

    <script>
        (function() {
            const slides = Array.from(document.querySelectorAll('.question-slide'));
            let current = 0;

            function show(idx) {
                slides.forEach((el, i) => {
                    el.style.display = (i === idx) ? '' : 'none';
                });
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
                lbl.textContent = (current >= slides.length - 1) ? 'Hoàn thành' : 'Next';
                status.textContent = `Câu ${current + 1} / ${slides.length}`;

                if (jumpList) {
                    jumpList.innerHTML = '';
                    for (let i = 0; i < slides.length; ++i) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className =
                            `w-full text-left px-4 py-2 text-sm border-b border-gray-100 hover:bg-blue-50 focus:bg-blue-100 ${i === current ? 'bg-purple-100 font-bold' : ''}`;
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
                jumpBtn.addEventListener('click', function() {
                    jumpList.classList.toggle('hidden');
                });
                document.addEventListener('click', function(e) {
                    if (!jumpBtn.contains(e.target) && !jumpList.contains(e.target)) {
                        jumpList.classList.add('hidden');
                    }
                });
            }

            document.getElementById('full-random-next').addEventListener('click', function() {
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
                        let correctAnswer = null;

                        // Parse metadata
                        if (qblock && qblock.dataset.metadata) {
                            try {
                                meta = JSON.parse(qblock.dataset.metadata);
                            } catch (e) {
                                console.error('JSON parsing error for qid', qid, ':', e.message);
                                meta = {};
                            }
                        }

                        // Detect part
                        if (qblock && qblock.dataset.part) {
                            part = parseInt(qblock.dataset.part);
                        } else if (meta && meta.part) {
                            part = parseInt(meta.part);
                        } else {
                            const partText = slide.querySelector('.mb-2.text-xs.text-gray-500')?.textContent;
                            if (partText) {
                                const match = partText.match(/Part\s+(\d+)/i);
                                if (match) part = parseInt(match[1]);
                            }
                        }

                        // Correct answers by part
                        if (meta) {
                            if (part === 1 && meta.correct_index !== undefined) {
                                correctAnswer = meta.correct_index;
                            } else if (part === 2) {
                                correctAnswer = meta.answers || meta.correct_order || null;
                            } else if (part === 3) {
                                correctAnswer = meta.answers || null;
                            } else if (part === 4 && meta.questions) {
                                correctAnswer = meta.questions.map(q => q.correct_index);
                            }
                        }

                        // User answers
                        let userAnswer = null;
                        if (part === 1) {
                            const checked = qblock?.querySelector('input[type=radio]:checked');
                            userAnswer = checked ? checked.value : null;
                        } else if (part === 2) {
                            const selects = qblock?.querySelectorAll('select.part2-select, select.speaker-select');
                            if (selects && selects.length > 0) {
                                userAnswer = Array.from(selects).map(s => s.value);
                                while (userAnswer.length < 4) userAnswer.push('');
                                if (userAnswer.every(val => val === '')) userAnswer = null;
                            }
                        } else if (part === 3) {
                            const selects = qblock?.querySelectorAll('select');
                            if (selects && selects.length > 0) {
                                userAnswer = Array.from(selects).map(s => s.value).filter(v => v !== '');
                                if (userAnswer.length === 0) userAnswer = null;
                            }
                        } else if (part === 4) {
                            // Part 4 uses radio inputs with name="selected[index]"
                            const radios = qblock?.querySelectorAll('input[type="radio"]:checked');
                            if (radios && radios.length > 0) {
                                // Create array based on the name attribute to preserve order
                                const answers = [];
                                radios.forEach(radio => {
                                    const match = radio.name.match(/selected\[(\d+)\]/);
                                    if (match) {
                                        answers[parseInt(match[1])] = radio.value;
                                    }
                                });
                                // Fill missing indices with null to match correctAnswer length
                                if (Array.isArray(correctAnswer)) {
                                    for (let i = 0; i < correctAnswer.length; i++) {
                                        if (answers[i] === undefined) {
                                            answers[i] = null;
                                        }
                                    }
                                }
                                userAnswer = answers;
                            } else {
                                // No radio checked, create null array with same length as correctAnswer
                                userAnswer = Array.isArray(correctAnswer) 
                                    ? new Array(correctAnswer.length).fill(null) 
                                    : null;
                            }
                        }

                        // Check correctness
                        let correct = false;
                        try {
                            if (part === 1 && userAnswer !== null && correctAnswer !== null) {
                                correct = parseInt(userAnswer) === parseInt(correctAnswer);
                            } else if ((part === 2 || part === 3) &&
                                Array.isArray(userAnswer) && Array.isArray(correctAnswer)) {
                                correct = userAnswer.length === correctAnswer.length &&
                                    userAnswer.every((val, idx) => parseInt(val) === parseInt(correctAnswer[idx]));
                            } else if (part === 4 &&
                                Array.isArray(userAnswer) && Array.isArray(correctAnswer)) {
                                correct = userAnswer.length === correctAnswer.length &&
                                    userAnswer.every((val, idx) => {
                                        if (val === undefined || val === null || val === '') {
                                            return false; // User didn't answer this sub-question
                                        }
                                        return parseInt(val) === parseInt(correctAnswer[idx]);
                                    });
                            }
                        } catch (e) {
                            // Silent error handling - log to console in development only
                            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                                console.error('Error calculating correctness for qid', qid, ':', e);
                            }
                            correct = false;
                        }

                        return { qid, part, correct, userAnswer, correctAnswer };
                    });

                    // Submit result
                    (async function() {
                        try {
                            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                            const res = await fetch('{{ route('listening.full-random.submit') }}', {
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
                            if (nextBtn) {
                                nextBtn.disabled = true;
                                nextBtn.dataset.orig = nextBtn.innerHTML;
                                nextBtn.innerHTML = 'Đang lưu...';
                            }

                            if (res.ok && json.success) {
                                if (json.redirect) {
                                    window.location.href = json.redirect;
                                } else if (json.attempt) {
                                    window.location.href = '/listening/full-random/result/' + json.attempt;
                                } else {
                                    window.location.href = '/listening/full-random/result';
                                }
                            } else {
                                console.error('Failed to save results:', json);
                                alert('Không thể lưu kết quả. Vui lòng thử lại.');
                                if (nextBtn) {
                                    nextBtn.disabled = false;
                                    nextBtn.innerHTML = nextBtn.dataset.orig || 'Hoàn thành';
                                }
                            }
                        } catch (e) {
                            console.error('Save results error:', e);
                            alert('Có lỗi khi lưu kết quả. Vui lòng thử lại.');
                            const nextBtn = document.getElementById('full-random-next');
                            if (nextBtn) {
                                nextBtn.disabled = false;
                                nextBtn.innerHTML = nextBtn.dataset.orig || 'Hoàn thành';
                            }
                        }
                    })();
                }
            });

            document.getElementById('full-random-prev').addEventListener('click', function() {
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
