<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('view-analytics');
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->recipients)) {
            $this->merge([
                'recipients' => collect(explode(',', $this->recipients))
                    ->map(fn ($email) => trim($email))
                    ->filter()
                    ->values()
                    ->all(),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'survey_id' => ['nullable', Rule::exists('surveys', 'id')->where('client_id', $this->input('client_id'))],
            'type' => ['required', 'in:pdf,excel,csv'],
            'frequency' => ['required', 'in:weekly,monthly,quarterly'],
            'recipients' => ['required', 'array', 'min:1'],
            'recipients.*' => ['email'],
        ];
    }
}
