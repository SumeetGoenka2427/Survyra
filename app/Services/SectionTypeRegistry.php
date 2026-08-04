<?php

namespace App\Services;

use App\Contracts\SectionTypeContract;
use InvalidArgumentException;

class SectionTypeRegistry
{
    /**
     * @var array<string, SectionTypeContract>
     */
    private array $resolved = [];

    public function resolve(string $key): SectionTypeContract
    {
        if (isset($this->resolved[$key])) {
            return $this->resolved[$key];
        }

        $class = config("section_types.{$key}");

        if (! $class) {
            throw new InvalidArgumentException("No section type registered for key [{$key}].");
        }

        return $this->resolved[$key] = app($class);
    }

    /**
     * @return array<int, SectionTypeContract>
     */
    public function all(): array
    {
        return collect(config('section_types'))
            ->keys()
            ->map(fn (string $key) => $this->resolve($key))
            ->all();
    }
}
