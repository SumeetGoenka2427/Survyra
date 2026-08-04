<?php

namespace App\SectionTypes;

class ContactFormSectionType extends AbstractSectionType
{
    public function key(): string
    {
        return 'contact_form';
    }

    public function label(): string
    {
        return 'Contact Form';
    }

    public function category(): string
    {
        return 'conversion';
    }

    public function availableStyles(): array
    {
        return [
            'default' => 'Card',
            'split' => 'Info Panel + Form',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'website-sections.contact_form.'.$this->resolveStyle($style);
    }

    public function validationRules(array $content): array
    {
        return [
            'heading' => ['nullable', 'string', 'max:255'],
            'intro' => ['nullable', 'string', 'max:500'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.key' => ['required', 'string', 'max:50'],
            'fields.*.label' => ['required', 'string', 'max:100'],
            'fields.*.type' => ['required', 'string', 'in:text,email,tel,textarea'],
            'fields.*.required' => ['boolean'],
        ];
    }

    public function defaultContent(): array
    {
        return [
            'heading' => 'Get in Touch',
            'intro' => "We'd love to hear from you.",
            'fields' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                ['key' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'required' => false],
                ['key' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true],
            ],
        ];
    }
}
