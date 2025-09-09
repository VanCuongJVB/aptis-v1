<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\ReadingSet;
use App\Models\Question;

class ListeningSetSeeder extends Seeder
{
    public function run(): void
    {
        // === QUIZ FULL TEST ===
        $quizFull = Quiz::firstOrCreate(
            ['skill' => 'listening', 'part' => 0],
            ['title' => 'Listening Practice - Full Test', 'description' => 'Full test with 17 questions', 'is_published' => true, 'duration_minutes' => 30]
        );

        // SET 1 (Q1-13)
        $set1Full = ReadingSet::firstOrCreate(['quiz_id' => $quizFull->id, 'title' => 'Set 1 (Q1-13)'], ['description' => 'Questions 1–13', 'is_public' => true, 'question_limit' => 13, 'order' => 1]);
        for ($i=1;$i<=13;$i++) {
            $options = ["Option A {$i}", "Option B {$i}", "Option C {$i}"];
            Question::create([
                'quiz_id'=>$quizFull->id, 'reading_set_id'=>$set1Full->id,
                'stem'=>"Q{$i}: Listen and choose the best answer.", 'skill'=>'listening','part'=>1,'type'=>'listening_mc','order'=>$i,
                'metadata'=>['options'=>$options,'correct_index'=>$i%3],
            ]);
        }

        // SET 2 (Q14)
        $set2Full = ReadingSet::firstOrCreate(['quiz_id'=>$quizFull->id,'title'=>'Set 2 (Q14)'], ['description'=>'Sentence completion','is_public'=>true,'question_limit'=>1,'order'=>2]);
        Question::create([
            'quiz_id'=>$quizFull->id,'reading_set_id'=>$set2Full->id,'stem'=>'Q14: Four people are talking. Complete the sentences.','skill'=>'listening','part'=>2,'type'=>'listening_speakers_complete','order'=>14,
            'metadata'=>['speakers'=>[['id'=>'A','label'=>'Speaker A'],['id'=>'B','label'=>'Speaker B']], 'items'=>['Sentence 1','Sentence 2','Sentence 3','Sentence 4','Sentence 5','Sentence 6'],'options'=>['Phrase 1','Phrase 2','Phrase 3','Phrase 4','Phrase 5','Phrase 6'],'answers'=>[0,1,2,3]],
        ]);

        // SET 3 (Q15)
        $set3Full = ReadingSet::firstOrCreate(['quiz_id'=>$quizFull->id,'title'=>'Set 3 (Q15)'], ['description'=>'Who expresses which opinion','is_public'=>true,'question_limit'=>1,'order'=>3]);
        Question::create([
            'quiz_id'=>$quizFull->id,'reading_set_id'=>$set3Full->id,'stem'=>'Q15: Who expresses which opinion?','skill'=>'listening','part'=>3,'type'=>'listening_who_expresses','order'=>15,
            'metadata'=>['options'=>['Man','Woman','Both'],'items'=>['Children need more sleep','Parents should support sports','Diet is very important','Quiet time improves focus','Screen time is harmful'],'answers'=>[0,1,2,2,1]],
        ]);

        // SET 4 (Q16–17)
        $set4Full = ReadingSet::firstOrCreate(['quiz_id'=>$quizFull->id,'title'=>'Set 4 (Q16-17)'], ['description'=>'Two multiple-choice items','is_public'=>true,'question_limit'=>2,'order'=>4]);
        Question::create(['quiz_id'=>$quizFull->id,'reading_set_id'=>$set4Full->id,'stem'=>'Q16: Opinion of the plan?','skill'=>'listening','part'=>4,'type'=>'listening_mc','order'=>16,'metadata'=>['options'=>['Similar','No consultation','Not representative'],'correct_index'=>1]]);
        Question::create(['quiz_id'=>$quizFull->id,'reading_set_id'=>$set4Full->id,'stem'=>'Q17: Woman thought of the speech?','skill'=>'listening','part'=>4,'type'=>'listening_mc','order'=>17,'metadata'=>['options'=>['Genuine','Prepared','Embarrassing'],'correct_index'=>0]]);

        // === CLONE TO QUIZES PART 1-4 ===
        $parts = [
            1 => [$set1Full],
            2 => [$set2Full],
            3 => [$set3Full],
            4 => [$set4Full],
        ];

        foreach ($parts as $part => $sets) {
            $quizPart = Quiz::firstOrCreate(
                ['skill'=>'listening','part'=>$part],
                ['title'=>"Listening Practice - Part {$part}",'description'=>"Practice quiz for Part {$part}",'is_published'=>true,'duration_minutes'=>10]
            );

            foreach ($sets as $setFull) {
                $setClone = ReadingSet::firstOrCreate(
                    ['quiz_id'=>$quizPart->id,'title'=>$setFull->title],
                    ['description'=>$setFull->description,'is_public'=>true,'question_limit'=>$setFull->question_limit,'order'=>$setFull->order]
                );

                foreach ($setFull->questions as $q) {
                    Question::create([
                        'quiz_id'=>$quizPart->id,
                        'reading_set_id'=>$setClone->id,
                        'stem'=>$q->stem,
                        'skill'=>$q->skill,
                        'part'=>$quizPart->part,
                        'type'=>$q->type,
                        'order'=>$q->order,
                        'metadata'=>$q->metadata,
                    ]);
                }
            }
        }

        echo "\nListening full test + 4 part quizzes seeded.\n";
    }
}
