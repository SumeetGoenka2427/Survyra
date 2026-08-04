<?php

namespace App\Http\Requests\Portal;

use App\Models\Website;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWebsiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Drop empty platform fields rather than storing blank strings, so
        // the footer's "only show icons with a real link" check stays simple.
        $this->merge([
            'social_links' => array_filter((array) $this->input('social_links', [])),
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'favicon_path' => ['nullable', 'url', 'max:2048'],
            'og_image' => ['nullable', 'url', 'max:2048'],
            'social_links' => ['array'],
        ];

        foreach (array_keys(Website::socialPlatforms()) as $platform) {
            $rules["social_links.{$platform}"] = ['nullable', 'url', 'max:255'];
        }

        return $rules;
    }
}
