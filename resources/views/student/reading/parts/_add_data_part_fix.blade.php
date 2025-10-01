@once
@push('scripts')
<script>
    (function(){
        document.querySelectorAll('[data-result-part]').forEach(function(section) {
            var partNumber = section.getAttribute('data-result-part');
            if (partNumber) {
                section.querySelectorAll('.question-block').forEach(function(qBlock) {
                    qBlock.setAttribute('data-part', partNumber);
                });
            }
        });
    })();
</script>
@endpush
@endonce