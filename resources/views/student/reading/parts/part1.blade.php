<div>
    {{-- Part 1: gap-filling using paragraphs and a shared BLANK options list --}}
    @php
        $meta = $question->metadata ?? [];
        $paragraphs = $meta['paragraphs'] ?? [];
        $choices = $meta['choices'] ?? [];
        $blankKeys = $meta['blank_keys'] ?? [];
        $selected = $answer->metadata['selected'] ?? [];

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

        $blankCount = count($blankKeys) ? count($blankKeys) : (is_array($choices) ? count($choices) : 0);
    @endphp

    @foreach($paragraphs as $pIndex => $para)
        @php
            $rendered = $para;
            foreach($choices as $i => $opts) {
                $blankToken = '[BLANK'.($i + 1).']';

                $selName = "part1_choice[{$i}]";
                $selHtml = '<select name="'.e($selName).'" class="border rounded px-2 py-1 inline-block">';
                $current = $selected[$i] ?? null;
                $selHtml .= '<option value="">-</option>';
                foreach($opts as $opt) {
                    $optEsc = e($opt);
                    $isSel = ($current !== null && $current === $opt) ? ' selected' : '';
                    $selHtml .= "<option value=\"{$optEsc}\"{$isSel}>{$optEsc}</option>";
                }
                $selHtml .= '</select>';

                $rendered = str_replace($blankToken, $selHtml, $rendered);
            }
        @endphp

        <p class="mb-2">{!! nl2br($rendered) !!}</p>
    @endforeach

    <div class="inline-feedback mt-3 text-sm text-gray-700" data-qid-feedback="{{ $question->id }}"></div>

</div>

@includeWhen(true, 'student.reading.parts._check_helper')
