<?php

namespace App\QuestionTypes;

class EmailQuestionType extends AbstractQuestionType
{
    public function key(): string
    {
        return 'email';
    }

    public function label(): string
    {
        return 'Email';
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
        return 'survey-questions.email.'.$this->resolveStyle($style);
    }

    public function validationRules(array $settings, bool $required): array
    {
        return [$this->requiredRule($required), 'email'];
    }

    public function score(mixed $answer, array $settings): ?float
    {
        return null;
    }
}
