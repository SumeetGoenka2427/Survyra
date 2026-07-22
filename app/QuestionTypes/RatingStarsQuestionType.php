<?php

namespace App\QuestionTypes;

class RatingStarsQuestionType extends AbstractQuestionType
{
    public function key(): string
    {
        return 'rating_stars';
    }

    public function label(): string
    {
        return 'Rating (Stars)';
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
            'stars' => 'Stars',
            'hearts' => 'Hearts',
            'numbers' => 'Numbered Badges',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'survey-questions.rating-stars.'.$this->resolveStyle($style);
    }

    public function validationRules(array $settings, bool $required): array
    {
        $max = $settings['max_stars'] ?? 5;

        return [$this->requiredRule($required), 'integer', 'min:1', "max:{$max}"];
    }

    public function score(mixed $answer, array $settings): ?float
    {
        return is_numeric($answer) ? (float) $answer : null;
    }
}
