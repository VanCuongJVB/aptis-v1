<div>
    {{-- Part 1: gap-filling using paragraphs and a shared BLANK options list --}}
    @php
        $meta = $question->metadata ?? [];
        $paragraphs = $meta['paragraphs'] ?? [];
        $choices = $meta['choices'] ?? [];
        $blankKeys = $meta['blank_keys'] ?? [];
        // previous answers (attempt answer may store metadata.selected as array)
        $selected = $answer->metadata['selected'] ?? [];

        // Determine shared options pool: prefer explicit `blank_options`,
        // otherwise flatten the per-blank choices into a unique list.
        $blankOptions = $meta['blank_options'] ?? null;
        if (! $blankOptions) {
            if (is_array($choices) && count($choices) > 0 && is_array(reset($choices))) {
                $flat = [];
                foreach ($choices as $opts) {
                    foreach ($opts as $o) {
                        $flat[] = $o;
                    }
                }
                $blankOptions = array_values(array_unique($flat));
            } else {
                $blankOptions = $choices ?? [];
            }
        }

        // Number of blanks to render. If blankKeys present, use its length;
        // otherwise fall back to the number of per-blank choice groups or 0.
        $blankCount = count($blankKeys) ? count($blankKeys) : (is_array($choices) ? count($choices) : 0);
    @endphp

    @foreach($paragraphs as $pIndex => $para)
        @php
            // Render inline selects for blanks. Blanks are marked in text as [BLANK1], [BLANK2], ...
            $rendered = $para;
            foreach($choices as $i => $opts) {
                $blankToken = '[BLANK'.($i + 1).']';

                // build select HTML for this blank
                $selName = "part1_choice[{$i}]";
                $selHtml = '<select name="'.e($selName).'" class="border rounded px-2 py-1 inline-block">';
                $current = $selected[$i] ?? null;
                // leading placeholder '-' with empty value so no real option is auto-selected
                $selHtml .= '<option value="">-</option>';
                foreach($opts as $opt) {
                    $optEsc = e($opt);
                    $isSel = ($current !== null && $current === $opt) ? ' selected' : '';
                    $selHtml .= "<option value=\"{$optEsc}\"{$isSel}>{$optEsc}</option>";
                }
                $selHtml .= '</select>';

                // replace all occurrences of the blank token in this paragraph
                $rendered = str_replace($blankToken, $selHtml, $rendered);
            }
        @endphp

        <p class="mb-2">{!! nl2br($rendered) !!}</p>
    @endforeach

    {{-- per-question controls: single check/next for the whole question --}}
    @php $partVar = $question->part ?? ($question->metadata['part'] ?? null); @endphp
    <div class="flex items-center space-x-2 mt-3">
        <button type="button" class="btn-base btn-primary part1-check" data-qid="{{ $question->id }}">Kiểm tra</button>
            @if(! in_array($partVar, [1,2,3,4]))
                <button type="button" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 part1-next" data-qid="{{ $question->id }}" disabled>Tiếp theo</button>
            @endif
    </div>

    <div class="inline-feedback mt-3 text-sm text-gray-700" data-qid-feedback="{{ $question->id }}"></div>

</div>
