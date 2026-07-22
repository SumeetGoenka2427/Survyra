<?php

namespace App\QuestionTypes;

class DropdownQuestionType extends AbstractQuestionType
{
    public function key(): string
    {
        return 'dropdown';
    }

    public function label(): string
    {
        return 'Dropdown (Single Choice)';
    }

    public function scoringType(): string
    {
        return 'none';
    }

    public function builderGroup(): string
    {
        return 'choice';
    }

    public function availableStyles(): array
    {
        return [
            'select' => 'Native Dropdown',
            'buttons' => 'Outline Buttons',
            'pills' => 'Pill Chips',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'survey-questions.dropdown.'.$this->resolveStyle($style);
    }

    public function validationRules(array $settings, bool $required): array
    {
        return [$this->requiredRule($required), 'string', 'in:'.implode(',', $settings['options'] ?? [])];
    }

    public function score(mixed $answer, array $settings): ?float
    {
        return null;
    }
}
