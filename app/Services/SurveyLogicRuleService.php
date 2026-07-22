<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyLogicRule;

class SurveyLogicRuleService
{
    public function create(Survey $survey, array $data): SurveyLogicRule
    {
        return $survey->logicRules()->create($data);
    }

    public function update(SurveyLogicRule $rule, array $data): SurveyLogicRule
    {
        $rule->update($data);

        return $rule;
    }

    public function delete(SurveyLogicRule $rule): void
    {
        $rule->delete();
    }
}
