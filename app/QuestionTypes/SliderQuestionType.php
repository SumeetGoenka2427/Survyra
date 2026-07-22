<?php

namespace App\QuestionTypes;

class SliderQuestionType extends AbstractQuestionType
{
    public function key(): string
    {
        return 'slider';
    }

    public function label(): string
    {
        return 'Slider (Drag a Scale)';
    }

    public function scoringType(): string
    {
        return 'scale';
    }

    public function builderGroup(): string
    {
        return 'scale';
    }

    public function availableStyles(): array
    {
        return [
            'range' => 'Drag Slider',
            'buttons' => 'Number Boxes',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'survey-questions.slider.'.$this->resolveStyle($style);
    }

    public function validationRules(array $settings, bool $required): array
    {
        $min = $settings['scale_min'] ?? 0;
        $max = $settings['scale_max'] ?? 10;

        return [$this->requiredRule($required), 'integer', "between:{$min},{$max}"];
    }

    public function score(mixed $answer, array $settings): ?float
    {
        return is_numeric($answer) ? (float) $answer : null;
    }
}
