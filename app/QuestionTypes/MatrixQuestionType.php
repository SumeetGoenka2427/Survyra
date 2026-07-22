<?php

namespace App\QuestionTypes;

class MatrixQuestionType extends AbstractQuestionType
{
    public function key(): string
    {
        return 'matrix';
    }

    public function label(): string
    {
        return 'Matrix (Rate Multiple Rows)';
    }

    public function scoringType(): string
    {
        return 'none';
    }

    public function builderGroup(): string
    {
        return 'matrix';
    }

    public function availableStyles(): array
    {
        return [
            'table' => 'Grid Table',
            'stacked' => 'Stacked Rows',
        ];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return 'survey-questions.matrix.'.$this->resolveStyle($style);
    }

    public function validationRules(array $settings, bool $required): array
    {
        $min = $settings['scale_min'] ?? 1;
        $max = $settings['scale_max'] ?? 5;
        $rowCount = count($settings['options'] ?? []);

        $rules = ['array'];

        if ($required) {
            $rules[] = "size:{$rowCount}";
        }

        $rules[] = function (string $attribute, mixed $value, \Closure $fail) use ($min, $max) {
            if (! is_array($value)) {
                return;
            }

            foreach ($value as $rating) {
                if (! is_numeric($rating) || $rating < $min || $rating > $max) {
                    $fail("Each row must be rated between {$min} and {$max}.");

                    return;
                }
            }
        };

        return $rules;
    }

    /**
     * Composite score: the average of every row's rating, so a matrix
     * question can still feed reports that expect a single numeric score.
     */
    public function score(mixed $answer, array $settings): ?float
    {
        if (! is_array($answer) || $answer === []) {
            return null;
        }

        $numeric = array_filter($answer, 'is_numeric');

        return $numeric === [] ? null : round(array_sum($numeric) / count($numeric), 2);
    }
}
