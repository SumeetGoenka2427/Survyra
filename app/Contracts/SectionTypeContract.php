<?php

namespace App\Contracts;

interface SectionTypeContract
{
    public function key(): string;

    public function label(): string;

    /**
     * UI palette grouping shown in the section-picker.
     *
     * hero | content | media | social_proof | conversion
     */
    public function category(): string;

    /**
     * Display styles this type supports, as style key => human label.
     * Types with only one look return ['default' => 'Default'].
     *
     * @return array<string, string>
     */
    public function availableStyles(): array;

    /**
     * Blade view used to render this section on the public website page.
     * $style is one of the keys from availableStyles(); an unknown/missing key
     * falls back to a sensible default, never to a missing view.
     */
    public function renderComponent(string $style = 'default'): string;

    /**
     * Server-side validation of this section's content shape on save
     * (not the public visitor's form submission - see ContactForm).
     *
     * @return array<string, array<int, string>>
     */
    public function validationRules(array $content): array;

    /**
     * Placeholder content seeded when this section type is first dropped
     * onto a page, so a new block never renders empty.
     */
    public function defaultContent(): array;
}
