<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateThankyouRuleRequest;
use App\Models\Survey;
use App\Services\SurveyThankyouRuleService;
use Illuminate\Http\RedirectResponse;

class SurveyThankyouRuleController extends Controller
{
    public function __construct(private readonly SurveyThankyouRuleService $rules)
    {
    }

    public function update(UpdateThankyouRuleRequest $request, Survey $survey, string $sentiment): RedirectResponse
    {
        $this->rules->updateForSentiment($survey, $sentiment, $request->validated());

        return back()->with('status', ucfirst($sentiment).' thank-you rule updated.');
    }
}
