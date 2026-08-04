<?php

namespace App\SectionTypes;

class TextBlockSectionType extends AbstractSectionType
{
    public function key(): string
    {
        return 'text_block';
    }

    public function label(): string
    {
        return 'Text Block';
    }

    public function category(): string
    {
        return 'content';
    }

    public function availableStyles(): array
    {
        return [
            'default' => 'Single Column',
            'two-column' => 'Two Column',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'website-sections.text_block.'.$this->resolveStyle($style);
    }

    public function validationRules(array $content): array
    {
        return [
            'heading' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ];
    }

    public function defaultContent(): array
    {
        return [
            'heading' => 'About Us',
            'body' => 'Share your story here.',
        ];
    }
}
