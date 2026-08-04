<?php

namespace App\SectionTypes;

class HeroSectionType extends AbstractSectionType
{
    public function key(): string
    {
        return 'hero';
    }

    public function label(): string
    {
        return 'Hero Banner';
    }

    public function category(): string
    {
        return 'hero';
    }

    public function availableStyles(): array
    {
        return [
            'centered' => 'Centered',
            'split-image' => 'Split with Image',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'website-sections.hero.'.$this->resolveStyle($style);
    }

    public function validationRules(array $content): array
    {
        return [
            'heading' => ['required', 'string', 'max:255'],
            'subheading' => ['nullable', 'string', 'max:500'],
            'cta_text' => ['nullable', 'string', 'max:100'],
            'cta_link' => ['nullable', 'array'],
            'background_image' => ['nullable', 'string'],
        ];
    }

    public function defaultContent(): array
    {
        return [
            'heading' => 'Welcome to Your Business',
            'subheading' => 'Tell visitors what makes you great.',
            'cta_text' => 'Get in Touch',
            'cta_link' => null,
            'background_image' => null,
        ];
    }
}
