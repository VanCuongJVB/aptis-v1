<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\ReadingSet;
use App\Models\Question;
use App\Models\Option;

class ReadingSetSeeder extends Seeder
{
    public function run(): void
    {
        // Create 4 reading quizzes (part 1..4)
        $parts = [1, 2, 3, 4];

        foreach ($parts as $part) {
            $quiz = Quiz::firstOrCreate([
                'skill' => 'reading',
                'part' => $part,
            ], [
                'title' => 'Reading Part ' . $part,
                'description' => 'Seeded reading quiz part ' . $part,
                'is_published' => true,
                'duration_minutes' => 15,
                'show_explanation' => true,
            ]);

            // Create one set per part for these specific seeded questions
            $set = ReadingSet::firstOrCreate([
                'quiz_id' => $quiz->id,
                'title' => "Default Set for Part $part"
            ], [
                'skill' => 'reading',
                'description' => "Auto generated default set for part $part",
                'is_public' => true,
                'order' => 1,
            ]);

            // Seed per-part question content
            if ($part === 1) {
                // Part 1: gap-filling multiple choice using the passage provided by the user
                $paragraphs = [
                    "Dear Sally,",
                    "Tim and I are on holiday in Greece. We have a nice [BLANK1] of the sea from our hotel.",
                    "The weather is [BLANK2] and it’s really hot.",
                    "Yesterday we went on a [BLANK3] on the lake and caught some fish.",
                    "We had lunch and then we visited an old [BLANK4].",
                    "Tomorrow we are going to take a car and [BLANK5] around.",
                    "We are going to visit some shops and buy clothes.",
                    "Love,",
                    "Janice",
                ];

                // For each blank we provide selectable choices; correct_answers array is used by scoring
                $choices = [
                    ['view', 'room', 'garden'],    // BLANK1
                    ['sunny', 'cold', 'windy'],    // BLANK2
                    ['boat', 'walk', 'ride'],      // BLANK3
                    ['castle', 'museum', 'park'],  // BLANK4
                    ['drive', 'walk', 'cycle'],    // BLANK5
                ];

                $correct = ['view', 'sunny', 'boat', 'castle', 'drive'];

                $q = Question::create([
                    'quiz_id' => $quiz->id,
                    'reading_set_id' => $set->id,
                    'title' => null,
                    'stem' => 'Dear Sally (Gap fill)',
                    'explanation' => null,
                    'skill' => 'reading',
                    'part' => 1,
                    'type' => 'reading_gap_filling',
                    'order' => 1,
                    'metadata' => [
                        'paragraphs' => $paragraphs,
                        'choices' => $choices,
                        'correct_answers' => $correct,
                        // Provide blank keys so admin UI can place selects anywhere in paragraphs
                        'blank_keys' => ['BLANK1','BLANK2','BLANK3','BLANK4','BLANK5']
                    ],
                ]);

                // Extra sample for Part 1
                Question::create([
                    'quiz_id' => $quiz->id,
                    'reading_set_id' => $set->id,
                    'title' => null,
                    'stem' => 'Holiday letter (Gap fill) - sample 2',
                    'explanation' => null,
                    'skill' => 'reading',
                    'part' => 1,
                    'type' => 'reading_gap_filling',
                    'order' => 2,
                    'metadata' => [
                        'paragraphs' => [
                            "Hi Tom,",
                            "We visited the old town and I saw a beautiful [BLANK1].",
                            "The museum was full of interesting [BLANK2].",
                            "I bought a small [BLANK3] for you.",
                        ],
                        'choices' => [
                            ['statue','garden','view'],
                            ['paintings','buildings','books'],
                            ['gift','souvenir','postcard']
                        ],
                        'correct_answers' => ['view','paintings','souvenir'],
                        'blank_keys' => ['BLANK1','BLANK2','BLANK3']
                    ],
                ]);

            } elseif ($part === 2) {
                // Part 2: ordering (students will reorder 5 sentences)
                $stem = "Assignments submitting.";

                $sentences = [
                    "First, it is a good idea to read your work and correct spelling mistakes",
                    "When you have corrected all the mistakes, print out your assignment.",
                    "Also, remember to print a separate cover sheet and attach it to your assignment.",
                    "You hand your assignment and attached document to the front desk in the library.",
                    "A staff member will check and confirm that you completed it.",
                ];

                $q = Question::create([
                    'quiz_id' => $quiz->id,
                    'reading_set_id' => $set->id,
                    'title' => null,
                    'stem' => $stem,
                    'explanation' => null,
                    'skill' => 'reading',
                    'part' => 2,
                    'type' => 'reading_notice_matching',
                    'order' => 1,
                    'metadata' => [
                        'sentences' => $sentences,
                        'correct_order' => [0,1,2,3,4] // 0-based indexes representing the proper sequence
                    ],
                ]);

                // Extra sample for Part 2
                Question::create([
                    'quiz_id' => $quiz->id,
                    'reading_set_id' => $set->id,
                    'title' => null,
                    'stem' => 'Procedure ordering - sample 2',
                    'explanation' => null,
                    'skill' => 'reading',
                    'part' => 2,
                    'type' => 'reading_notice_matching',
                    'order' => 2,
                    'metadata' => [
                        'sentences' => [
                            'Open the box and remove all components.',
                            'Plug the power cable into the wall socket.',
                            'Attach the antenna to the back of the unit.',
                            'Turn on the power and wait for the green light.',
                            'Follow the on-screen setup instructions.'
                        ],
                        'correct_order' => [0,2,1,3,4]
                    ],
                ]);

            } elseif ($part === 3) {
                // Part 3: matching with four people and 7 questions
                $stem = "Four people respond in the comments section of an online magazine article about education and work. Read the texts and then answer the questions below.";

                $people = [
                    ['id' => 'A', 'name' => 'Petra', 'text' => "As you get older, responsibilities like a job and family dominate your life. It can be hard to balance things. Studying at university is demanding. So you should do it at an age when you are independent and carefree. It is also important to learn how the world of business works. Spending unpaid time in a company is a great way to get that experience. Any course that can give you an opportunity to do that is worth considering."],
                    ['id' => 'B', 'name' => 'Antonio', 'text' => "Life doesn’t really get serious until you hit your mid-twenties. Before that, try out different things and get some life experience. It’s only as you approach your thirties that you need to get serious about your career. That’s the time to start thinking about further education. Many colleges offer inexpensive courses for more mature students. Going back to student life for a year is a great idea, and you can then return to the world of work at management level."],
                    ['id' => 'C', 'name' => 'Eleanor', 'text' => "Nowadays, it is popular for school leavers to take a break before they think about an occupation or a place at university. I think the most important thing is to start working as soon as you can. You need practical experience for your CV, and that can be more valuable than a diploma. Nevertheless, your studies do not have to stop just because you are working. Colleges and universities offer options for people who want to do both."],
                    ['id' => 'D', 'name' => 'Jermaine', 'text' => "I think we should all keep learning, but you don’t need a piece of paper from an institution to prove it. There are many free courses available online. Of course, not all are good, but a little research will help you identify which one is best for you. A lot of young people get into debt because they have to pay for their studies. With the resources available online these days, you can take control. You won’t regret it."],
                ];

                $questions = [
                    ['text' => 'Who thinks you should study when you are older?', 'answer' => 'A'],
                    ['text' => 'Who thinks formal qualifications are too expensive?', 'answer' => 'D'],
                    ['text' => 'Who thinks you should go to university when you are young?', 'answer' => 'A'],
                    ['text' => 'Who thinks you should study independently?', 'answer' => 'D'],
                    ['text' => 'Who thinks you should combine a job with studying?', 'answer' => 'C'],
                    ['text' => 'Who thinks you should choose a course that is practical?', 'answer' => 'A'],
                    ['text' => 'Who thinks you should get a job immediately after leaving school?', 'answer' => 'C'],
                ];

                $q = Question::create([
                    'quiz_id' => $quiz->id,
                    'reading_set_id' => $set->id,
                    'title' => null,
                    'stem' => $stem,
                    'explanation' => null,
                    'skill' => 'reading',
                    'part' => 3,
                    'type' => 'reading_sentence_matching',
                    'order' => 1,
                    'metadata' => [
                        'people' => $people,
                        'items' => array_map(fn($i) => $i['text'], $questions),
                        'answers' => array_map(fn($i) => $i['answer'], $questions),
                    ],
                ]);

                // Extra sample for Part 3
                $people2 = [
                    ['id' => 'A', 'name' => 'Liam', 'text' => 'I think starting work early gives you a head start.'],
                    ['id' => 'B', 'name' => 'Maya', 'text' => 'Sometimes practical experience is more valuable than theory.'],
                    ['id' => 'C', 'name' => 'Noah', 'text' => 'Studying part-time while working can be a smart option.'],
                    ['id' => 'D', 'name' => 'Zoe', 'text' => 'Online courses are a good way to learn without debt.'],
                ];
                $questions2 = [
                    ['text' => 'Who recommends starting work early?', 'answer' => 'A'],
                    ['text' => 'Who prefers practical experience?', 'answer' => 'B'],
                    ['text' => 'Who suggests part-time study?', 'answer' => 'C'],
                ];

                Question::create([
                    'quiz_id' => $quiz->id,
                    'reading_set_id' => $set->id,
                    'title' => null,
                    'stem' => $stem,
                    'explanation' => null,
                    'skill' => 'reading',
                    'part' => 3,
                    'type' => 'reading_sentence_matching',
                    'order' => 2,
                    'metadata' => [
                        'people' => $people2,
                        'items' => array_map(fn($i) => $i['text'], $questions2),
                        'answers' => array_map(fn($i) => $i['answer'], $questions2),
                    ],
                ]);

            } elseif ($part === 4) {
                // Part 4: long text multiple choice (7 paragraphs, choose 7 answers from 8 options)
                $stem = "Mission to Mars";

                $paragraphs = [
                    "On 3 June 2010, an international crew of six astronauts entered a spaceship and prepared themselves for a 520-day voyage to the planet Mars and back. The module that was to be their home for the next year and a half contained their sleeping quarters, a kitchen/dining room, a living room, a control room and a toilet. There was also space for food storage, a small greenhouse, a bathroom, a sauna and even a gym. The Mars landing was scheduled for 12 February 2011, following a 255-day flight, and would involve a full two days of exploration of the planet's surface. An equally long return journey would see the astronauts return to Earth on 4 November 2011.",
                    "Emerging from the spaceship after an exhausting 520 days, Russian commander Alexei Sitev declared the mission finally over. 'The programme has been fully carried out', he announced at a press conference. 'All the crew members are in good health. We are now ready for further tests'. Indeed, the general consensus in the scientific community was that the Mars 500 project had achieved its aims, and, what is more, the crew had managed to complete their mission without ever having to leave the earth’s atmosphere.",
                    "Mars 500 was, in fact, a simulation exercise. The astronauts never even left the ground, and their spaceship was a specially constructed working model situated in a warehouse in the suburbs of Moscow. The aims of the mission were to see how well humans could cope with the confinement and stress involved in extended interplanetary travel. The astronauts – three Russians, a Frenchman, an Italian and a Chinese national – were volunteers for the project, and although all of them had the option of leaving their 550-cubic metre living space at any time, none of them chose to do so.",
                    "All communications between the crew and mission control were subject to a twenty-minute delay to simulate the time it would take signals to reach the earth from outer space. Although not all the elements of space flight – such as the effects of zero gravity – could be reproduced, the conditions on board were made as realistic as possible. The astronauts breathed recycled air, showered only once every ten days and lived mostly on a diet of tinned food. Even the surface of Mars had been recreated to allow the crew the simulated experience of walking on the red planet.",
                    "In addition to the discomforts of living in a confined space, the astronauts also had to endure the psychological stresses brought about by isolation and boredom. Scientific studies have already shown that extended periods of social isolation can disrupt the normal mechanisms of the body. This can lead to increased levels of stress and higher blood pressure, which, in turn, can create feelings of anxiety and aggression. The astronauts were subject to regular medical tests throughout the experiment and they were under constant observation via a twenty-four hour closed-circuit television system. The tests continued even after the men had completed their mission as the scientists were interested to see how the astronauts would cope with a return to normal life.",
                    "The data collected by the experiment is further evidence that human beings are capable of overcoming the pressures of long space flight that will be necessary if future exploration of planets is to be feasible. Although there is resistance in some quarters to investment in space exploration, some scientists believe that our future lies in the stars. With the world's population exceeding seven billion and showing no sign of slowing down, future generations may be forced to seek out new worlds beyond our own increasingly overcrowded planet.",
                    "Although the dry and dusty landscape of Mars may not be the most suitable spot for future habitation, there are other planets that could sustain human life. To date about 700 planets with similarities to Earth have been identified outside our own solar system, and about 15 of these are potentially habitable. The most recent to be discovered – Kepler 22-b – has a surface temperature of about 22°C and orbits a star not unlike our own sun. Scientists believe that it may even contain water. However, although it may seem like a good candidate for a future space colony, it is 600 light years away, and so it is likely to remain beyond human reach for many generations to come."
                ];

                $options = [
                    'There was also space for food storage, a small greenhouse, a bathroom, a sauna and even a gym.',
                    "The programme has been fully carried out",
                    'The astronauts never even left the ground',
                    'All communications between the crew and mission control were subject to a twenty-minute delay',
                    'The astronauts were subject to regular medical tests throughout the experiment',
                    'The data collected by the experiment is further evidence that human beings are capable of overcoming the pressures of long space flight',
                    'Although the dry and dusty landscape of Mars may not be the most suitable spot for future habitation',
                    'To date about 700 planets with similarities to Earth have been identified'
                ];

                $q = Question::create([
                    'quiz_id' => $quiz->id,
                    'reading_set_id' => $set->id,
                    'title' => null,
                    'stem' => $stem,
                    'explanation' => null,
                    'skill' => 'reading',
                    'part' => 4,
                    'type' => 'reading_long_text',
                    'order' => 1,
                    'metadata' => [
                        'paragraphs' => $paragraphs,
                        'options' => $options,
                        'correct' => [0,1,2,3,4,5,6] // indicate which of the 8 options are correct for the 7 questions (example)
                    ],
                ]);

                // Extra sample for Part 4
                $paragraphs2 = [
                    'A team of researchers tested plants in a controlled environment.',
                    'The experiment showed how crops respond to changes in light and temperature.',
                    'Further trials are planned to measure long-term effects.'
                ];
                $options2 = [
                    'Researchers used a greenhouse for experiments.',
                    'The study focused on light and temperature.',
                    'Long-term trials are unnecessary.'
                ];

                Question::create([
                    'quiz_id' => $quiz->id,
                    'reading_set_id' => $set->id,
                    'title' => null,
                    'stem' => 'Plant trials - sample',
                    'explanation' => null,
                    'skill' => 'reading',
                    'part' => 4,
                    'type' => 'reading_long_text',
                    'order' => 2,
                    'metadata' => [
                        'paragraphs' => $paragraphs2,
                        'options' => $options2,
                        'correct' => [0,1]
                    ],
                ]);
            }
        }

        // Optional: create a "Full" reading quiz that references all parts
        Quiz::firstOrCreate([
            'skill' => 'reading',
            'part' => 0,
        ], [
            'title' => 'Reading Full Test',
            'description' => 'Full reading test containing all parts',
            'is_published' => true,
            'duration_minutes' => 60,
            'show_explanation' => true,
        ]);

        echo "\nReading sets and sample questions seeded.\n";
    }
}
