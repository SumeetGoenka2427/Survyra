<?php

namespace App\QuestionTypes;

class NpsQuestionType extends AbstractQuestionType
{
    public function key(): string
    {
        return 'nps';
    }

    public function label(): string
    {
        return 'NPS (0-10 Recommendation Score)';
    }

    public function scoringType(): string
    {
        return 'nps';
    }

    public function builderGroup(): string
    {
        return 'scale';
    }

    public function availableStyles(): array
    {
        return [
            'numbers' => 'Number Boxes',
            'emoji' => 'Emoji Scale',
            'circles' => 'Circular Buttons',
            'gradient' => 'Gradient Row',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'survey-questions.nps.'.$this->resolveStyle($style);
    }

    public function validationRules(array $settings, bool $required): array
    {
        return [$this->requiredRule($required), 'integer', 'min:0', 'max:10'];
    }

    public function score(mixed $answer, array $settings): ?float
    {
        return is_numeric($answer) ? (float) $answer : null;
    }
}
