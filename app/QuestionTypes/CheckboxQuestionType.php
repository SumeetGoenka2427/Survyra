<?php

namespace App\QuestionTypes;

class CheckboxQuestionType extends AbstractQuestionType
{
    public function key(): string
    {
        return 'checkbox';
    }

    public function label(): string
    {
        return 'Checkbox (Multiple Choice)';
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
            'boxes' => 'Checkboxes',
            'cards' => 'Modern Cards',
            'pills' => 'Pill Chips',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'survey-questions.checkbox.'.$this->resolveStyle($style);
    }

    public function validationRules(array $settings, bool $required): array
    {
        return [$this->requiredRule($required), 'array'];
    }

    public function score(mixed $answer, array $settings): ?float
    {
        return null;
    }
}
