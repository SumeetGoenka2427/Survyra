<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWebsiteThemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'theme_id' => ['nullable', 'integer', 'exists:website_themes,id'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
            'background' => ['nullable', 'string', 'max:20'],
            'heading_font' => ['nullable', 'string', 'max:100'],
            'body_font' => ['nullable', 'string', 'max:100'],
            'header_style' => ['nullable', 'string', 'max:50'],
            'button_style' => ['nullable', 'string', 'max:50'],
            'border_radius' => ['nullable', 'integer', 'min:0', 'max:50'],
            'container_width' => ['nullable', 'string', 'max:50'],
            'custom_css' => ['nullable', 'string'],
        ];
    }
}
