<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTemplateQuestionRequest;
use App\Models\SurveyTemplate;
use App\Models\SurveyTemplateQuestion;
use App\Services\SurveyTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class SurveyTemplateQuestionController extends Controller
{
    public function __construct(private readonly SurveyTemplateService $templates)
    {
    }

    public function store(StoreTemplateQuestionRequest $request, SurveyTemplate $template): RedirectResponse
    {
        $this->templates->addQuestion($template, $request->validated());

        return back()->with('status', 'Question added.');
    }

    public function update(StoreTemplateQuestionRequest $request, SurveyTemplate $template, SurveyTemplateQuestion $question): RedirectResponse
    {
        $this->assertBelongsToTemplate($template, $question);

        $this->templates->updateQuestion($question, $request->validated());

        return back()->with('status', 'Question updated.');
    }

    public function destroy(SurveyTemplate $template, SurveyTemplateQuestion $question): RedirectResponse
    {
        $this->authorize('update', $template);
        $this->assertBelongsToTemplate($template, $question);

        $this->templates->removeQuestion($question);

        return back()->with('status', 'Question removed.');
    }

    public function moveUp(SurveyTemplate $template, SurveyTemplateQuestion $question): RedirectResponse
    {
        $this->authorize('update', $template);
        $this->assertBelongsToTemplate($template, $question);

        $this->templates->moveQuestionUp($question);

        return back();
    }

    public function moveDown(SurveyTemplate $template, SurveyTemplateQuestion $question): RedirectResponse
    {
        $this->authorize('update', $template);
        $this->assertBelongsToTemplate($template, $question);

        $this->templates->moveQuestionDown($question);

        return back();
    }

    private function assertBelongsToTemplate(SurveyTemplate $template, SurveyTemplateQuestion $question): void
    {
        if ($question->survey_template_id !== $template->id) {
            throw ValidationException::withMessages(['question' => 'Question does not belong to this template.']);
        }
    }
}
