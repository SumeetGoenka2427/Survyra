<?php

namespace App\Http\Requests;

use App\Models\WebsiteSection;
use Illuminate\Foundation\Http\FormRequest;

class StoreWebsiteContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'company_website' => ['prohibited'],
            'section_id' => ['required', 'integer', 'exists:website_sections,id'],
            'page_id' => ['required', 'integer', 'exists:website_pages,id'],
        ];

        $section = WebsiteSection::query()->find($this->input('section_id'));
        $fields = $section->content['fields'] ?? [];

        foreach ($fields as $field) {
            if (empty($field['key'])) {
                continue;
            }

            $type = $field['type'] ?? 'text';
            $rules[$field['key']] = [
                ($field['required'] ?? false) ? 'required' : 'nullable',
                $type === 'email' ? 'email' : 'string',
                'max:2000',
            ];
        }

        return $rules;
    }
}
