<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'survey_id' => ['nullable', Rule::exists('surveys', 'id')->where('client_id', $this->user()->client_id)],
            'type' => ['required', 'in:pdf,excel,csv'],
            'frequency' => ['required', 'in:weekly,monthly,quarterly'],
            'recipients' => ['required', 'array', 'min:1'],
            'recipients.*' => ['email'],
        ];
    }
}
