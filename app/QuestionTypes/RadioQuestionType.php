<?php

namespace App\QuestionTypes;

class RadioQuestionType extends AbstractQuestionType
{
    public function key(): string
    {
        return 'radio';
    }

    public function label(): string
    {
        return 'Radio (Single Choice)';
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
            'buttons' => 'Outline Buttons',
            'cards' => 'Modern Cards',
            'pills' => 'Pill Chips',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'survey-questions.radio.'.$this->resolveStyle($style);
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
