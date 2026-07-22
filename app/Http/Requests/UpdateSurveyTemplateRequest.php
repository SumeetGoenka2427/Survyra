<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSurveyTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('template'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'industry_category' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'layout' => ['sometimes', 'in:multi_step,conversational,one_page,card_based,section_wizard'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
