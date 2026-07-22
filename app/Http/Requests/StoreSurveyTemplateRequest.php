<?php

namespace App\Http\Requests;

use App\Models\SurveyTemplate;
use Illuminate\Foundation\Http\FormRequest;

class StoreSurveyTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', SurveyTemplate::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'industry_category' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'layout' => ['sometimes', 'in:multi_step,conversational,one_page,card_based,section_wizard'],
        ];
    }
}
