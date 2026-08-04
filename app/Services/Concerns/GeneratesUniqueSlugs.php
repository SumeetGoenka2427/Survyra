<?php

namespace App\Services\Concerns;

use Illuminate\Support\Str;

trait GeneratesUniqueSlugs
{
    /**
     * Slugifies $text and appends a numeric suffix until $exists (given a
     * candidate slug) returns false. $exists is scoped by the caller (e.g.
     * globally unique vs. unique-per-website).
     */
    protected function uniqueSlug(string $text, callable $exists): string
    {
        $base = Str::slug($text) ?: Str::lower(Str::random(8));
        $slug = $base;
        $suffix = 2;

        while ($exists($slug)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
