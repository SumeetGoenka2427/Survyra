<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;

class StoreWebsiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'theme_id' => ['nullable', 'integer', 'exists:website_themes,id'],
            'template_id' => ['nullable', 'integer', 'exists:website_templates,id'],
        ];
    }
}
