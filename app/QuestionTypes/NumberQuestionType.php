<?php

namespace App\QuestionTypes;

class NumberQuestionType extends AbstractQuestionType
{
    public function key(): string
    {
        return 'number';
    }

    public function label(): string
    {
        return 'Number';
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
            'floating' => 'Floating Label',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'survey-questions.number.'.$this->resolveStyle($style);
    }

    public function validationRules(array $settings, bool $required): array
    {
        return [$this->requiredRule($required), 'numeric'];
    }

    public function score(mixed $answer, array $settings): ?float
    {
        return null;
    }
}
