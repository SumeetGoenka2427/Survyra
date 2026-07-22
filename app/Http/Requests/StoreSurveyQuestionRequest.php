<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSurveyQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('survey'));
    }

    protected function prepareForValidation(): void
    {
        $options = collect(explode("\n", (string) $this->input('options_text')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        $settings = array_filter([
            'scale_min' => $this->input('scale_min'),
            'scale_max' => $this->input('scale_max'),
            'low_label' => $this->input('low_label'),
            'high_label' => $this->input('high_label'),
            'max_stars' => $this->input('max_stars'),
            'display_style' => $this->input('display_style'),
        ], fn ($value) => $value !== null && $value !== '');

        $this->merge([
            'options' => $options,
            'settings' => $settings,
            'is_required' => $this->boolean('is_required'),
        ]);
    }

    public function rules(): array
    {
        return [
            'question_type_id' => ['required', 'exists:question_types,id'],
            'question_text' => ['required', 'string', 'max:500'],
            'is_required' => ['boolean'],
            'options' => ['array'],
            'options.*' => ['string', 'max:255'],
            'settings' => ['array'],
        ];
    }
}
