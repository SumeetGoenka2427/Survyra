<?php

namespace App\Services;

use App\Contracts\QuestionTypeContract;
use InvalidArgumentException;

class QuestionTypeRegistry
{
    /**
     * @var array<string, QuestionTypeContract>
     */
    private array $resolved = [];

    public function resolve(string $key): QuestionTypeContract
    {
        if (isset($this->resolved[$key])) {
            return $this->resolved[$key];
        }

        $class = config("question_types.{$key}");

        if (! $class) {
            throw new InvalidArgumentException("No question type registered for key [{$key}].");
        }

        return $this->resolved[$key] = app($class);
    }

    /**
     * @return array<int, QuestionTypeContract>
     */
    public function all(): array
    {
        return collect(config('question_types'))
            ->keys()
            ->map(fn (string $key) => $this->resolve($key))
            ->all();
    }
}
