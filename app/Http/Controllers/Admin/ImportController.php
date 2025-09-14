<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Quiz;
use App\Models\ReadingSet;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    protected array $allowedSkills = ['reading', 'listening'];

    /**
     * Clear existing quizzes/sets/questions/options depending on DB driver so IDs restart from 1
     * Throws on failure.
     */
    private function clearExistingData()
    {
        try {
            $driver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } catch (\Throwable $e) {
            $driver = null;
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::statement('DELETE FROM options');
            DB::statement('DELETE FROM questions');
            DB::statement('DELETE FROM "sets"');
            DB::statement('DELETE FROM quizzes');
            DB::statement("DELETE FROM sqlite_sequence WHERE name IN ('options','questions','sets','quizzes')");
            DB::statement('PRAGMA foreign_keys = ON');
            return;
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::statement('TRUNCATE TABLE options');
            DB::statement('TRUNCATE TABLE questions');
            DB::statement('TRUNCATE TABLE `sets`');
            DB::statement('TRUNCATE TABLE quizzes');
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return;
        }

        if ($driver === 'pgsql' || $driver === 'postgres') {
            DB::statement('TRUNCATE TABLE options, questions, "sets", quizzes RESTART IDENTITY CASCADE');
            return;
        }

        // Generic fallback
        DB::table('options')->delete();
        DB::table('questions')->delete();
        DB::table('sets')->delete();
        DB::table('quizzes')->delete();
    }

    private function importQuiz(array $qData)
    {
        $skill = $this->normalizeSkill($qData['skill'] ?? null, 'reading');
        $part = $this->coerceInt($qData['part'] ?? null, 0);
        $duration = $this->coerceInt($qData['duration_minutes'] ?? null, 45);
        $showExplanation = isset($qData['show_explanation']) ? (bool)$qData['show_explanation'] : true;

        $quiz = Quiz::create([
            'title' => $qData['title'] ?? 'Untitled Quiz',
            'description' => $qData['description'] ?? null,
            'skill' => $skill,
            'part' => $part,
            'is_published' => $qData['is_published'] ?? false,
            'duration_minutes' => $duration,
            'show_explanation' => $showExplanation,
            'metadata' => $qData['metadata'] ?? null,
        ]);

        if (!empty($qData['sets']) && is_array($qData['sets'])) {
            foreach ($qData['sets'] as $sData) {
                $this->importSet($quiz, $sData, $skill);
            }
        }
    }

    private function importSet(Quiz $quiz, array $sData, string $quizSkill)
    {
        $setOrder = $this->coerceInt($sData['order'] ?? null, 0);
        $rawSetSkill = $sData['skill'] ?? $quizSkill;
        $setSkill = $this->normalizeSkill($rawSetSkill, $quizSkill);

        $set = ReadingSet::create([
            'quiz_id' => $quiz->id,
            'title' => $sData['title'] ?? 'Set',
            'skill' => $setSkill,
            'order' => $setOrder,
            'metadata' => $sData['metadata'] ?? null,
        ]);

        if (!empty($sData['questions']) && is_array($sData['questions'])) {
            foreach ($sData['questions'] as $idx => $ques) {
                $this->importQuestion($quiz, $set, $ques, $idx, $sData);
            }
        }
    }

    private function importQuestion(Quiz $quiz, ReadingSet $set, array $ques, int $index, array $sData)
    {
        $qOrder = $this->coerceInt($ques['order'] ?? null, $index);

        // Start with provided metadata; prefer provided metadata.* over top-level fields
        $qMeta = $ques['metadata'] ?? [];

        // If audio given at top-level, copy into metadata and also set audio_path column
        $audioPath = null;
        if (!empty($ques['audio'])) {
            $qMeta = is_array($qMeta) ? $qMeta : (array)$qMeta;
            $qMeta['audio'] = $ques['audio'];
            $audioPath = $ques['audio'];
        }

        $rawSkill = $ques['skill'] ?? $sData['skill'] ?? $quiz->skill ?? null;
        $qSkill = $this->normalizeSkill($rawSkill, $set->skill ?? 'reading');

        // determine part: question -> set -> quiz
        $qPart = $this->coerceInt($ques['part'] ?? $sData['part'] ?? $quiz->part ?? null, $quiz->part ?? 0);

        $point = $this->coerceInt($ques['point'] ?? null, 1);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'reading_set_id' => $set->id,
            'title' => $ques['title'] ?? null,
            'stem' => $ques['stem'] ?? null,
            'type' => $ques['type'] ?? null,
            'order' => $qOrder,
            'skill' => $qSkill,
            'part' => $qPart,
            'point' => $point,
            'audio_path' => $audioPath,
            'metadata' => $qMeta,
        ]);

        // Create Option model rows only when the author provided a top-level 'options' array
        // Many seeded questions keep options inside metadata (metadata.options) — in that case we preserve metadata and do not create Option rows.
        if (!empty($ques['options']) && is_array($ques['options'])) {
            foreach ($ques['options'] as $optIndex => $opt) {
                $isCorrect = false;
                // support top-level 'correct' index or boolean markers per option
                if (isset($ques['correct']) && $ques['correct'] === $optIndex) {
                    $isCorrect = true;
                }
                // support metadata.correct_index pointing to an index
                if (isset($qMeta['correct_index']) && $qMeta['correct_index'] === $optIndex) {
                    $isCorrect = true;
                }

                Option::create([
                    'question_id' => $question->id,
                    'label' => chr(65 + $optIndex),
                    'content' => is_array($opt) ? json_encode($opt) : $opt,
                    'is_correct' => $isCorrect,
                    'order' => is_numeric($optIndex) ? (int)$optIndex : 0,
                ]);
            }
        }
    }

    private function normalizeSkill($value, $fallback = 'reading')
    {
        $v = is_string($value) ? strtolower($value) : null;
        if ($v && in_array($v, $this->allowedSkills)) {
            return $v;
        }
        return $fallback;
    }

    private function coerceInt($value, $fallback = 0)
    {
        if ($value === null || $value === '') {
            return $fallback;
        }
        return is_numeric($value) ? (int)$value : $fallback;
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json,txt',
        ]);

        $json = json_decode(file_get_contents($request->file('file')->getRealPath()), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->with('error', 'Invalid JSON file');
        }

        if (empty($json['quizzes']) || !is_array($json['quizzes'])) {
            return back()->with('error', 'JSON must contain a top-level "quizzes" array');
        }
        // delegate to helper methods
        try {
            $this->clearExistingData();
            DB::beginTransaction();
            foreach ($json['quizzes'] as $qData) {
                $this->importQuiz($qData);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Import failed: ' . $e->getMessage(), [
                'exception' => $e,
                'file' => isset($qData) ? $qData : null,
            ]);
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.quizzes.index')->with('success', 'Import completed');
    }

    /**
     * Dry-run import: validate structure and return a summary without modifying DB.
     */
    public function dryRun(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:json,txt',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->all()], 422);
        }

        $content = file_get_contents($request->file('file')->getRealPath());
        $json = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['success' => false, 'errors' => ['Invalid JSON: ' . json_last_error_msg()]], 422);
        }

        if (empty($json['quizzes']) || !is_array($json['quizzes'])) {
            return response()->json(['success' => false, 'errors' => ['JSON must contain a top-level "quizzes" array']], 422);
        }

        $quizzes = $json['quizzes'];
        $problems = [];
        $setsCount = 0;
        $questionsCount = 0;

        foreach ($quizzes as $qIdx => $q) {
            if (empty($q['title']) && empty($q['description'])) {
                $problems[] = "Quiz #{$qIdx} is missing a title/description";
            }
            if (!empty($q['sets']) && is_array($q['sets'])) {
                $setsCount += count($q['sets']);
                foreach ($q['sets'] as $sIdx => $s) {
                    if (empty($s['title'])) {
                        $problems[] = "Quiz #{$qIdx} Set #{$sIdx} is missing a title";
                    }
                    if (!empty($s['questions']) && is_array($s['questions'])) {
                        $questionsCount += count($s['questions']);
                        foreach ($s['questions'] as $tIdx => $t) {
                            // Basic heuristics: MCQ should have options; written should have stem/content
                            if (isset($t['type']) && strtolower($t['type']) === 'mcq') {
                                if (empty($t['options']) || !is_array($t['options']) || count($t['options']) < 2) {
                                    $problems[] = "Quiz #{$qIdx} Set #{$sIdx} Question #{$tIdx} (mcq) should have at least 2 options";
                                }
                            } else {
                                if (empty($t['stem']) && empty($t['title'])) {
                                    $problems[] = "Quiz #{$qIdx} Set #{$sIdx} Question #{$tIdx} is missing stem/title";
                                }
                            }
                        }
                    }
                }
            }
        }

        $summary = [
            'quizzes' => count($quizzes),
            'sets' => $setsCount,
            'questions' => $questionsCount,
            'problems' => $problems,
        ];

        return response()->json(['success' => true, 'summary' => $summary]);
    }

    /**
     * Export the current quizzes/sets/questions/options as a JSON template users can download and edit.
     */
    public function exportTemplate()
    {
        $quizzes = Quiz::with(['sets.questions.options'])->get();

        $payload = ['quizzes' => []];
        foreach ($quizzes as $quiz) {
            $q = $quiz->toArray();
            // convert relationships into nested structure compatible with importer
            $q['sets'] = [];
            foreach ($quiz->sets as $set) {
                $s = $set->toArray();
                $s['questions'] = [];
                foreach ($set->questions as $question) {
                    $qq = $question->toArray();
                    $qq['options'] = [];
                    foreach ($question->options as $opt) {
                        $qq['options'][] = $opt->toArray();
                    }
                    $s['questions'][] = $qq;
                }
                $q['sets'][] = $s;
            }
            $payload['quizzes'][] = $q;
        }

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="aptis_export_' . date('Ymd_His') . '.json"'
        ]);
    }
}
