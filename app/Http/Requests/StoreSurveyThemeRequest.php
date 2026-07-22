<?php

namespace App\Http\Requests;

use App\Models\SurveyTheme;
use Illuminate\Foundation\Http\FormRequest;

class StoreSurveyThemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', SurveyTheme::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'primary_color' => ['required', 'string', 'max:20'],
            'secondary_color' => ['required', 'string', 'max:20'],
            'background' => ['required', 'string', 'max:20'],
            'button_style' => ['required', 'in:rounded,square,pill'],
            'font' => ['required', 'string', 'max:100'],
            'progress_bar_style' => ['required', 'in:bar,dots,steps'],
            'border_radius' => ['required', 'integer', 'min:0', 'max:32'],
            'custom_css' => ['nullable', 'string', 'max:5000', 'not_regex:/<script|javascript:|expression\s*\(/i'],
        ];
    }
}
