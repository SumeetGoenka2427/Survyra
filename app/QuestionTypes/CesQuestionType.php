<?php

namespace App\QuestionTypes;

class CesQuestionType extends AbstractQuestionType
{
    public function key(): string
    {
        return 'ces';
    }

    public function label(): string
    {
        return 'CES (Customer Effort Score)';
    }

    public function scoringType(): string
    {
        return 'ces';
    }

    public function builderGroup(): string
    {
        return 'scale';
    }

    public function availableStyles(): array
    {
        return [
            'numbers' => 'Number Boxes',
            'circles' => 'Circular Buttons',
            'gradient' => 'Gradient Row',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'survey-questions.ces.'.$this->resolveStyle($style);
    }

    public function validationRules(array $settings, bool $required): array
    {
        $max = $settings['scale_max'] ?? 7;

        return [$this->requiredRule($required), 'integer', 'min:1', "max:{$max}"];
    }

    public function score(mixed $answer, array $settings): ?float
    {
        return is_numeric($answer) ? (float) $answer : null;
    }
}
