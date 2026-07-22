<?php

namespace App\QuestionTypes;

class DateQuestionType extends AbstractQuestionType
{
    public function key(): string
    {
        return 'date';
    }

    public function label(): string
    {
        return 'Date';
    }

    public function scoringType(): string
    {
        return 'none';
    }

    public function builderGroup(): string
    {
        return 'plain';
    }

    public function availableStyles(): array
    {
        return [
            'modern' => 'Modern Input',
            'labeled' => 'Labeled Input',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'survey-questions.date.'.$this->resolveStyle($style);
    }

    public function validationRules(array $settings, bool $required): array
    {
        return [$this->requiredRule($required), 'date'];
    }

    public function score(mixed $answer, array $settings): ?float
    {
        return null;
    }
}
