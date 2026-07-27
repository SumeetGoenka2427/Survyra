<?php

namespace App\Http\Requests;

use App\Models\Survey;
use Illuminate\Foundation\Http\FormRequest;

class StoreSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Survey::class);
    }

    public function rules(): array
    {
        $rules = [
            'client_id' => ['required', 'exists:clients,id'],
            'title' => ['required', 'string', 'max:255'],
            'mode' => ['required', 'in:template,blank'],
            'ai_questions' => ['nullable', 'string'],
            'theme_id' => ['nullable', 'exists:survey_themes,id'],
        ];

        if ($this->input('mode') === 'template') {
            $rules['survey_template_id'] = ['required', 'exists:survey_templates,id'];
        } else {
            $rules['layout'] = ['required', 'in:multi_step,conversational,one_page,card_based,section_wizard'];
        }

        return $rules;
    }
}
