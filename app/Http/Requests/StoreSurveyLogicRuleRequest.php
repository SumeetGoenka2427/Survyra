<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSurveyLogicRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('survey'));
    }

    /**
     * source_question_id isn't a separate form field - it's derived from the
     * first condition, so the builder UI doesn't need a redundant selector.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'source_question_id' => $this->input('conditions.0.question_id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'source_question_id' => ['required', 'exists:survey_questions,id'],
            'condition_operator' => ['required', 'in:AND,OR'],
            'conditions' => ['required', 'array', 'min:1'],
            'conditions.*.question_id' => ['required', 'exists:survey_questions,id'],
            'conditions.*.operator' => ['required', 'in:equals,not_equals,contains,greater_than,less_than,is_empty,is_not_empty'],
            'conditions.*.value' => ['nullable', 'string', 'max:255'],
            'action' => ['required', 'in:show,hide,jump_to_question,end_survey'],
            'target_question_id' => ['nullable', 'exists:survey_questions,id'],
        ];
    }
}
