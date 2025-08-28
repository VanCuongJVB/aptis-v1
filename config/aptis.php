<?php

return [
    'skills' => [
        'reading' => [
            1 => ['key' => 'reading_p1_dropdown', 'label' => 'Part 1 – Sentence completion', 'types' => ['dropdown']],
            2 => ['key' => 'reading_p2_ordering', 'label' => 'Part 2 – Text cohesion',       'types' => ['ordering']],
            3 => ['key' => 'reading_p3_matching', 'label' => 'Part 3 – Opinion matching',    'types' => ['matching']],
            4 => ['key' => 'reading_p4_headings', 'label' => 'Part 4 – Headings matching',   'types' => ['heading_matching']],
        ],
        'listening' => [
            1 => ['key' => 'listening_p1_mcq', 'label' => 'Part 1 – MCQ', 'types' => ['mcq_single']],
            2 => ['key' => 'listening_p2_mcq', 'label' => 'Part 2 – MCQ', 'types' => ['mcq_single']],
            3 => ['key' => 'listening_p3_mcq', 'label' => 'Part 3 – MCQ', 'types' => ['mcq_single']],
            4 => ['key' => 'listening_p4_mcq', 'label' => 'Part 4 – MCQ', 'types' => ['mcq_single']],
        ],
    ],
    'question_types' => ['dropdown', 'ordering', 'matching', 'heading_matching', 'mcq_single'],
];
