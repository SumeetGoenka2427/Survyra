<?php

namespace App\SectionTypes;

use App\Contracts\SectionTypeContract;

abstract class AbstractSectionType implements SectionTypeContract
{
    /**
     * Single-look types (most of them) never override these - the one Blade
     * view living at website-sections/{key}.blade.php is always used.
     *
     * @return array<string, string>
     */
    public function availableStyles(): array
    {
        return ['default' => 'Default'];
    }

    public function renderComponent(string $style = 'default'): string
    {
        return "website-sections.{$this->key()}";
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
