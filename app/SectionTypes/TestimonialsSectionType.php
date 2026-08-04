<?php

namespace App\SectionTypes;

class TestimonialsSectionType extends AbstractSectionType
{
    public function key(): string
    {
        return 'testimonials';
    }

    public function label(): string
    {
        return 'Testimonials';
    }

    public function category(): string
    {
        return 'social_proof';
    }

    public function availableStyles(): array
    {
        return [
            'cards' => 'Cards',
            'single-quote' => 'Single Quote',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'website-sections.testimonials.'.$this->resolveStyle($style);
    }

    public function validationRules(array $content): array
    {
        return [
            'items' => ['nullable', 'array'],
            'items.*.quote' => ['required_with:items', 'string', 'max:1000'],
            'items.*.author' => ['nullable', 'string', 'max:255'],
            'items.*.role' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function defaultContent(): array
    {
        return [
            'items' => [
                ['quote' => 'Working with this business was a great experience.', 'author' => 'Happy Customer', 'role' => ''],
            ],
        ];
    }
}
