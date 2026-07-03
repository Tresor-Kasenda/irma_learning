<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\QuestionTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuestionRequest;
use App\Http\Requests\Admin\UpdateQuestionRequest;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class QuestionController extends Controller
{
    public function store(StoreQuestionRequest $request, Exam $exam): RedirectResponse
    {
        $data = $request->safe()->except('options');
        $maxPosition = $exam->questions()->max('order_position') ?? 0;
        $data['order_position'] = $maxPosition + 1;

        $question = $exam->questions()->create($data);

        foreach (array_values($request->validated('options')) as $index => $option) {
            $question->options()->create([
                'option_text' => $option['option_text'],
                'is_correct' => $option['is_correct'] ?? false,
                'order_position' => $option['order_position'] ?? $index + 1,
            ]);
        }

        // Validate option integrity based on question type
        if ($question->question_type === QuestionTypeEnum::SINGLE_CHOICE || $question->question_type === QuestionTypeEnum::TRUE_FALSE) {
            $correctCount = $question->options()->where('is_correct', true)->count();
            if ($correctCount !== 1) {
                $question->options()->delete();
                $question->delete();

                return back()->with('error', 'Une question '.$question->question_type->value.' doit avoir exactement une bonne réponse.');
            }
        }

        return back()->with('success', 'Question ajoutée.');
    }

    public function update(UpdateQuestionRequest $request, Exam $exam, Question $question): RedirectResponse
    {
        $data = $request->safe()->except('options');
        $question->update($data);

        $question->options()->delete();
        foreach (array_values($request->validated('options')) as $index => $option) {
            $question->options()->create([
                'option_text' => $option['option_text'],
                'is_correct' => $option['is_correct'] ?? false,
                'order_position' => $option['order_position'] ?? $index + 1,
            ]);
        }

        // Validate option integrity based on question type
        if ($question->question_type === QuestionTypeEnum::SINGLE_CHOICE || $question->question_type === QuestionTypeEnum::TRUE_FALSE) {
            $correctCount = $question->options()->where('is_correct', true)->count();
            if ($correctCount !== 1) {
                return back()->with('error', 'Une question '.$question->question_type->value.' doit avoir exactement une bonne réponse.');
            }
        }

        return back()->with('success', 'Question mise à jour.');
    }

    public function destroy(Exam $exam, Question $question): RedirectResponse
    {
        $question->options()->delete();
        $question->delete();

        return back()->with('success', 'Question supprimée.');
    }

    public function reorder(Request $request, Exam $exam): RedirectResponse
    {
        $request->validate([
            'questions' => ['required', 'array'],
            'questions.*.id' => ['required', 'integer', 'exists:questions,id'],
            'questions.*.order_position' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->input('questions') as $item) {
            Question::query()->whereKey($item['id'])->update(['order_position' => $item['order_position']]);
        }

        return back()->with('success', 'Questions réordonnées.');
    }
}
