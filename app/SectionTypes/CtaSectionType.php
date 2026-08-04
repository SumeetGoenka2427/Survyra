<?php

namespace App\SectionTypes;

class CtaSectionType extends AbstractSectionType
{
    public function key(): string
    {
        return 'cta';
    }

    public function label(): string
    {
        return 'Call to Action';
    }

    public function category(): string
    {
        return 'conversion';
    }

    public function availableStyles(): array
    {
        return [
            'default' => 'Gradient Banner',
            'minimal' => 'Bordered Box',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'website-sections.cta.'.$this->resolveStyle($style);
    }

    public function validationRules(array $content): array
    {
        return [
            'heading' => ['required', 'string', 'max:255'],
            'button_text' => ['required', 'string', 'max:100'],
            'button_link' => ['nullable', 'array'],
            'variant' => ['nullable', 'string', 'in:primary,secondary'],
        ];
    }

    public function defaultContent(): array
    {
        return [
            'heading' => 'Ready to get started?',
            'button_text' => 'Contact Us',
            'button_link' => null,
            'variant' => 'primary',
        ];
    }
}
