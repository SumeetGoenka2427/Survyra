<?php

namespace App\QuestionTypes;

class CsatQuestionType extends AbstractQuestionType
{
    public function key(): string
    {
        return 'csat';
    }

    public function label(): string
    {
        return 'CSAT (Satisfaction Score)';
    }

    public function scoringType(): string
    {
        return 'csat';
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
        return 'survey-questions.csat.'.$this->resolveStyle($style);
    }

    public function validationRules(array $settings, bool $required): array
    {
        $max = $settings['scale_max'] ?? 5;

        return [$this->requiredRule($required), 'integer', 'min:1', "max:{$max}"];
    }

    public function score(mixed $answer, array $settings): ?float
    {
        return is_numeric($answer) ? (float) $answer : null;
    }
}
