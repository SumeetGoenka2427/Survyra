<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;

class StoreWebsiteSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section_type_id' => ['required', 'integer', 'exists:section_types,id'],
            'style' => ['nullable', 'string', 'max:50'],
        ];
    }
}
