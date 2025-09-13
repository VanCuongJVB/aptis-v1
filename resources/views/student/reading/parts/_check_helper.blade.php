@once
    <template id="inline-feedback-tpl">
        <div class="flex items-start space-x-3">
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <div class="font-medium text-sm" data-title>Đã lưu</div>
                    <div class="text-xs font-semibold px-2 py-0.5 rounded-full text-gray-700 bg-gray-100" data-stats>
                        Đúng 0 / 0
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mt-2">
                    <div>
                        <div class="text-xs text-gray-500">Bạn chọn</div>
                        <div class="text-sm text-gray-700 mt-1" data-user>(chưa chọn)</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Đáp án</div>
                        <div class="text-sm text-gray-700 mt-1" data-correct>(không có)</div>
                    </div>
                </div>
            </div>
            <div data-badge></div>
        </div>
    </template>

    @include('student.reading.parts.renderers._all_renderers')

    @push('scripts')
        <script>
            // Simple helper to show inline feedback by cloning template
            window.inlineFeedback = {
                show: function (qid, userAnswer, correctAnswer, statsText) {
                    const tpl = document.getElementById('inline-feedback-tpl');
                    if (!tpl) return;
                    const target = document.querySelector(`.inline-feedback[data-qid-feedback="${qid}"]`);
                    if (!target) return;

                    // Add a guard to prevent multiple rendering
                    if (target.hasAttribute('data-feedback-rendered')) {
                        return; // Skip if feedback was already rendered for this target
                    }
                    target.setAttribute('data-feedback-rendered', 'true');

                    const node = tpl.content.cloneNode(true);
                    node.querySelector('[data-user]').textContent = userAnswer ?? '(chưa chọn)';
                    node.querySelector('[data-correct]').textContent = correctAnswer ?? '(không có)';
                    node.querySelector('[data-stats]').textContent = statsText ?? '';
                    target.innerHTML = '';
                    target.appendChild(node);
                    target.classList.remove('hidden');
                },
                hide: function (qid) {
                    const target = document.querySelector(`.inline-feedback[data-qid-feedback="${qid}"]`);
                    if (target) {
                        target.innerHTML = '';
                        target.classList.add('hidden');
                        target.removeAttribute('data-feedback-rendered');
                    }
                }
            };
        </script>
    @endpush
@endonce