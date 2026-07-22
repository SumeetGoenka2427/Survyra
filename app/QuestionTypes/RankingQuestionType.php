<?php

namespace App\QuestionTypes;

class RankingQuestionType extends AbstractQuestionType
{
    public function key(): string
    {
        return 'ranking';
    }

    public function label(): string
    {
        return 'Ranking (Reorder Items)';
    }

    public function scoringType(): string
    {
        return 'none';
    }

    public function builderGroup(): string
    {
        return 'choice';
    }

    public function validationRules(array $settings, bool $required): array
    {
        $items = $settings['options'] ?? [];

        $rules = ['array'];

        if ($required) {
            $rules[] = 'size:'.count($items);
        }

        $rules[] = function (string $attribute, mixed $value, \Closure $fail) use ($items) {
            if (! is_array($value) || $value === []) {
                return;
            }

            if (array_diff($items, $value) !== [] || array_diff($value, $items) !== []) {
                $fail('Please rank every item exactly once.');
            }
        };

        return $rules;
    }

    public function score(mixed $answer, array $settings): ?float
    {
        return null;
    }
}
