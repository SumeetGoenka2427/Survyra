<?php

namespace App\Http\Requests;

use App\Models\QuestionType;
use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('template'));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'options' => $this->isImageChoice() ? $this->imageOptionsFromJson() : $this->plainOptionsFromText(),
            'settings' => $this->normalizedSettings(),
            'is_required' => $this->boolean('is_required'),
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'question_type_id' => ['required', 'exists:question_types,id'],
            'question_text' => ['required', 'string', 'max:500'],
            'is_required' => ['boolean'],
            'options' => $this->isImageChoice() ? ['array', 'min:1'] : ['array'],
            'settings' => ['array'],
        ];

        if ($this->isImageChoice()) {
            $rules['options.*.label'] = ['required', 'string', 'max:255'];
            $rules['options.*.image'] = ['nullable', 'string', 'max:2048'];
            $rules['options.*.value'] = ['nullable', 'string', 'max:255'];
        } else {
            $rules['options.*'] = ['string', 'max:255'];
        }

        return $rules;
    }

    private function isImageChoice(): bool
    {
        return QuestionType::query()->find($this->input('question_type_id'))?->key === 'image_choice';
    }

    private function plainOptionsFromText(): array
    {
        return collect(explode("\n", (string) $this->input('options_text')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    private function imageOptionsFromJson(): array
    {
        $decoded = json_decode((string) $this->input('image_options_json'), true);

        return collect(is_array($decoded) ? $decoded : [])
            ->map(function ($row) {
                $label = trim((string) ($row['label'] ?? ''));

                if ($label === '') {
                    return null;
                }

                $value = trim((string) ($row['value'] ?? ''));

                return [
                    'label' => $label,
                    'image' => trim((string) ($row['image'] ?? '')),
                    'value' => $value !== '' ? $value : $label,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function normalizedSettings(): array
    {
        $settings = array_filter([
            'scale_min' => $this->input('scale_min'),
            'scale_max' => $this->input('scale_max'),
            'low_label' => $this->input('low_label'),
            'high_label' => $this->input('high_label'),
            'max_stars' => $this->input('max_stars'),
            'display_style' => $this->input('display_style'),
            'help_text' => $this->input('help_text'),
            'max_choices' => $this->input('max_choices'),
            'max_file_size' => $this->input('max_file_size'),
        ], fn ($value) => $value !== null && $value !== '');

        if ($this->boolean('multiple')) {
            $settings['multiple'] = true;
        }

        if ($this->filled('allowed_types')) {
            $settings['allowed_types'] = collect(explode(',', (string) $this->input('allowed_types')))
                ->map(fn ($type) => trim(strtolower($type)))
                ->filter()
                ->values()
                ->all();
        }

        return $settings;
    }
}
