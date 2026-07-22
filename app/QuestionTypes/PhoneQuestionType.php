<?php

namespace App\QuestionTypes;

class PhoneQuestionType extends AbstractQuestionType
{
    public function key(): string
    {
        return 'phone';
    }

    public function label(): string
    {
        return 'Phone';
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
        return 'survey-questions.phone.'.$this->resolveStyle($style);
    }

    public function validationRules(array $settings, bool $required): array
    {
        return [$this->requiredRule($required), 'string', 'max:20'];
    }

    public function score(mixed $answer, array $settings): ?float
    {
        return null;
    }
}
