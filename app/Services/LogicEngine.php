<?php

namespace App\Services;

use App\Models\SurveyLogicRule;

/**
 * Evaluates a survey_logic_rules row against an in-progress set of answers.
 * Pure function - no DB/session dependency, so it can be unit tested with
 * hand-built answer arrays. Not wired into any live page until Phase 4.
 */
class LogicEngine
{
    /**
     * @param  array<int, mixed>  $answers  question_id => answer value
     */
    public function evaluate(SurveyLogicRule $rule, array $answers): bool
    {
        $operator = strtoupper($rule->condition_operator ?? 'AND');

        if ($operator === 'OR') {
            foreach ($rule->conditions as $condition) {
                if ($this->conditionMatches($condition, $answers)) {
                    return true;
                }
            }
            return false;
        }

        // AND (default)
        foreach ($rule->conditions as $condition) {
            if (! $this->conditionMatches($condition, $answers)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array{question_id: int, operator: string, value?: mixed}  $condition
     * @param  array<int, mixed>  $answers
     */
    private function conditionMatches(array $condition, array $answers): bool
    {
        $answer = $answers[$condition['question_id']] ?? null;
        $expected = $condition['value'] ?? null;

        return match ($condition['operator']) {
            'equals' => $this->normalize($answer) === $this->normalize($expected),
            'not_equals' => $this->normalize($answer) !== $this->normalize($expected),
            'contains' => is_string($answer) && str_contains($answer, (string) $expected),
            'greater_than' => is_numeric($answer) && is_numeric($expected) && (float) $answer > (float) $expected,
            'less_than' => is_numeric($answer) && is_numeric($expected) && (float) $answer < (float) $expected,
            'is_empty' => $answer === null || $answer === '' || $answer === [],
            'is_not_empty' => $answer !== null && $answer !== '' && $answer !== [],
            default => false,
        };
    }

    private function normalize(mixed $value): mixed
    {
        return is_string($value) ? trim(mb_strtolower($value)) : $value;
    }
}
