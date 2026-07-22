<?php

namespace App\QuestionTypes;

use App\Contracts\QuestionTypeContract;

abstract class AbstractQuestionType implements QuestionTypeContract
{
    /**
     * Single-look types (most of them) never override these - the one Blade
     * view living at survey-questions/{key}.blade.php is always used.
     *
     * @return array<string, string>
     */
    public function availableStyles(): array
    {
        return ['default' => 'Default'];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return "survey-questions.{$this->key()}";
    }

    protected function requiredRule(bool $required): string
    {
        return $required ? 'required' : 'nullable';
    }

    /**
     * Helper for multi-style types: resolves an untrusted stored style key
     * against availableStyles(), falling back to the first (default) style
     * rather than ever returning a view path that doesn't exist.
     */
    protected function resolveStyle(string $style): string
    {
        $styles = $this->availableStyles();

        return array_key_exists($style, $styles) ? $style : array_key_first($styles);
    }
}
