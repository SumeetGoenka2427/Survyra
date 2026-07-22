<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyQuestionRequest;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Services\SurveyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SurveyQuestionController extends Controller
{
    public function __construct(private readonly SurveyService $surveys)
    {
    }

    public function store(StoreSurveyQuestionRequest $request, Survey $survey): RedirectResponse
    {
        $this->assertEditable($survey);

        $this->surveys->addQuestion($survey, $request->validated());

        return back()->with('status', 'Question added.');
    }

    public function update(StoreSurveyQuestionRequest $request, Survey $survey, SurveyQuestion $question): RedirectResponse
    {
        $this->assertBelongsToSurvey($survey, $question);
        $this->assertEditable($survey);

        $this->surveys->updateQuestion($question, $request->validated());

        return back()->with('status', 'Question updated.');
    }

    public function destroy(Survey $survey, SurveyQuestion $question): RedirectResponse
    {
        $this->authorize('update', $survey);
        $this->assertBelongsToSurvey($survey, $question);
        $this->assertEditable($survey);

        $this->surveys->removeQuestion($question);

        return back()->with('status', 'Question removed.');
    }

    public function duplicate(Survey $survey, SurveyQuestion $question): RedirectResponse
    {
        $this->authorize('update', $survey);
        $this->assertBelongsToSurvey($survey, $question);
        $this->assertEditable($survey);

        $this->surveys->duplicateQuestion($question);

        return back()->with('status', 'Question duplicated.');
    }

    public function reorder(Request $request, Survey $survey): JsonResponse
    {
        $this->authorize('update', $survey);
        $this->assertEditable($survey);

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.order' => ['required', 'integer', 'min:1'],
        ]);

        $this->surveys->reorderQuestions($survey, $validated['items']);

        return response()->json(['ok' => true]);
    }

    public function moveUp(Survey $survey, SurveyQuestion $question): RedirectResponse
    {
        $this->authorize('update', $survey);
        $this->assertBelongsToSurvey($survey, $question);
        $this->assertEditable($survey);

        $this->surveys->moveQuestionUp($question);

        return back();
    }

    public function moveDown(Survey $survey, SurveyQuestion $question): RedirectResponse
    {
        $this->authorize('update', $survey);
        $this->assertBelongsToSurvey($survey, $question);
        $this->assertEditable($survey);

        $this->surveys->moveQuestionDown($question);

        return back();
    }

    public function setPrimaryScore(Survey $survey, SurveyQuestion $question): RedirectResponse
    {
        $this->authorize('update', $survey);

        $this->surveys->setPrimaryScoreQuestion($survey, $question);

        return back()->with('status', 'Primary score question updated.');
    }

    private function assertBelongsToSurvey(Survey $survey, SurveyQuestion $question): void
    {
        if ($question->survey_id !== $survey->id) {
            throw ValidationException::withMessages(['question' => 'Question does not belong to this survey.']);
        }
    }

    private function assertEditable(Survey $survey): void
    {
        abort_if($this->surveys->hasResponses($survey), 403, 'This survey already has responses - its questions are locked to protect existing data.');
    }
}
