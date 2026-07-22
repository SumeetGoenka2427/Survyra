<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyLogicRuleRequest;
use App\Models\Survey;
use App\Models\SurveyLogicRule;
use App\Services\SurveyLogicRuleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class SurveyLogicRuleController extends Controller
{
    public function __construct(private readonly SurveyLogicRuleService $rules)
    {
    }

    public function store(StoreSurveyLogicRuleRequest $request, Survey $survey): RedirectResponse
    {
        $this->rules->create($survey, $request->validated());

        return back()->with('status', 'Logic rule added.');
    }

    public function update(StoreSurveyLogicRuleRequest $request, Survey $survey, SurveyLogicRule $rule): RedirectResponse
    {
        $this->assertBelongsToSurvey($survey, $rule);

        $this->rules->update($rule, $request->validated());

        return back()->with('status', 'Logic rule updated.');
    }

    public function destroy(Survey $survey, SurveyLogicRule $rule): RedirectResponse
    {
        $this->authorize('update', $survey);
        $this->assertBelongsToSurvey($survey, $rule);

        $this->rules->delete($rule);

        return back()->with('status', 'Logic rule removed.');
    }

    private function assertBelongsToSurvey(Survey $survey, SurveyLogicRule $rule): void
    {
        if ($rule->survey_id !== $survey->id) {
            throw ValidationException::withMessages(['rule' => 'Rule does not belong to this survey.']);
        }
    }
}
