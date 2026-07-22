<?php

namespace App\QuestionTypes;

class YesNoQuestionType extends AbstractQuestionType
{
    public function key(): string
    {
        return 'yes_no';
    }

    public function label(): string
    {
        return 'Yes/No';
    }

    public function scoringType(): string
    {
        return 'boolean';
    }

    public function builderGroup(): string
    {
        return 'plain';
    }

    public function availableStyles(): array
    {
        return [
            'buttons' => 'Buttons',
            'toggle' => 'Segmented Toggle',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'survey-questions.yes-no.'.$this->resolveStyle($style);
    }

    public function validationRules(array $settings, bool $required): array
    {
        return [$this->requiredRule($required), 'in:yes,no'];
    }

    public function score(mixed $answer, array $settings): ?float
    {
        return match ($answer) {
            'yes' => 1.0,
            'no' => 0.0,
            default => null,
        };
    }
}
