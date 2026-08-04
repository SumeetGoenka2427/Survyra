<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWebsiteSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['content' => $this->buildContent($this->route('section')->sectionType->key)]);
    }

    public function rules(): array
    {
        return [
            'style' => ['nullable', 'string', 'max:50'],
            'content' => ['array'],
        ];
    }

    /**
     * Each section type stores a differently-shaped content array (see the
     * corresponding SectionTypeContract::defaultContent()). The builder form
     * posts named fields matching that shape rather than raw JSON, so this
     * reassembles them the same way StoreSurveyQuestionRequest reassembles
     * per-question-type fields.
     */
    private function buildContent(string $key): array
    {
        return match ($key) {
            'hero' => [
                'heading' => (string) $this->input('heading', ''),
                'subheading' => $this->input('subheading') ?: null,
                'cta_text' => $this->input('cta_text') ?: null,
                'cta_link' => $this->linkFromInput('cta_link'),
                'background_image' => $this->input('background_image') ?: null,
            ],
            'text_block' => [
                'heading' => $this->input('heading') ?: null,
                'body' => (string) $this->input('body', ''),
            ],
            'gallery' => [
                'images' => $this->decodeRows('images_json'),
            ],
            'testimonials' => [
                'items' => $this->decodeRows('items_json'),
            ],
            'cta' => [
                'heading' => (string) $this->input('heading', ''),
                'button_text' => (string) $this->input('button_text', ''),
                'button_link' => $this->linkFromInput('button_link'),
                'variant' => $this->input('variant', 'primary'),
            ],
            'contact_form' => [
                'heading' => $this->input('heading') ?: null,
                'intro' => $this->input('intro') ?: null,
                'fields' => collect($this->decodeRows('fields_json'))
                    ->map(fn ($field) => array_merge($field, ['required' => (bool) ($field['required'] ?? false)]))
                    ->all(),
            ],
            default => [],
        };
    }

    private function linkFromInput(string $field): ?array
    {
        return match ($this->input("{$field}_type", 'none')) {
            'page' => $this->filled("{$field}_page_id") ? ['type' => 'page', 'page_id' => (int) $this->input("{$field}_page_id")] : null,
            'external' => $this->filled("{$field}_url") ? ['type' => 'external', 'url' => $this->input("{$field}_url")] : null,
            default => null,
        };
    }

    private function decodeRows(string $field): array
    {
        $decoded = json_decode((string) $this->input($field), true);

        return is_array($decoded) ? $decoded : [];
    }
}
