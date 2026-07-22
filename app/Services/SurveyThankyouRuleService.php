<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyThankyouRule;

class SurveyThankyouRuleService
{
    /**
     * Upserts the rule for a given sentiment (positive/neutral/negative).
     * "Never redirect unhappy customers to Google Reviews" is enforced here
     * too, as a second line of defense behind the Form Request.
     */
    public function updateForSentiment(Survey $survey, string $sentiment, array $data): SurveyThankyouRule
    {
        if ($sentiment === 'negative') {
            $data['show_google_review'] = false;
        }

        $rule = $survey->thankyouRules()->where('sentiment', $sentiment)->firstOrFail();
        $rule->update($data);

        return $rule;
    }
}
