<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('survey'));
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'theme_id' => ['nullable', 'exists:survey_themes,id'],
            'layout' => ['sometimes', 'in:multi_step,conversational,one_page,card_based,section_wizard'],
            'welcome_screen' => ['nullable', 'array'],
            'welcome_screen.title' => ['nullable', 'string', 'max:255'],
            'welcome_screen.description' => ['nullable', 'string', 'max:1000'],
            'welcome_screen.button_text' => ['nullable', 'string', 'max:100'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'max_responses' => ['nullable', 'integer', 'min:1'],
            'is_anonymous' => ['boolean'],
            'gdpr_enabled' => ['boolean'],
            'gdpr_text' => ['nullable', 'string', 'max:1000'],
            'privacy_policy_url' => ['nullable', 'url', 'max:500'],
            'ga_tracking_id' => ['nullable', 'string', 'max:100'],
            'meta_pixel_id' => ['nullable', 'string', 'max:100'],
        ];
    }
}
