<?php

namespace App\Contracts;

interface QuestionTypeContract
{
    public function key(): string;

    public function label(): string;

    /**
     * none | scale | nps | csat | ces | boolean
     */
    public function scoringType(): string;

    /**
     * Which shared settings-field group the template/question builder shows for this type.
     *
     * choice | scale | plain
     */
    public function builderGroup(): string;

    /**
     * Blade component used to render this question on the public survey page (Phase 4).
     * $style is one of the keys from availableStyles(); an unknown/missing key
     * falls back to a sensible default, never to a missing view.
     */
    public function renderComponent(string $style = 'default'): string;

    /**
     * Display styles this type supports, as style key => human label.
     * Types with only one look return ['default' => 'Default'].
     *
     * @return array<string, string>
     */
    public function availableStyles(): array;

    /**
     * @return array<int, string>
     */
    public function validationRules(array $settings, bool $required): array;

    public function score(mixed $answer, array $settings): ?float;
}
