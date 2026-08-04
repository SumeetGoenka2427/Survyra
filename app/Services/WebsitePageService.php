<?php

namespace App\Services;

use App\Models\Website;
use App\Models\WebsitePage;
use App\Services\Concerns\GeneratesUniqueSlugs;
use App\Services\Concerns\ReordersQuestions;
use InvalidArgumentException;

class WebsitePageService
{
    use GeneratesUniqueSlugs, ReordersQuestions;

    public function create(Website $website, array $data): WebsitePage
    {
        $isFirstPage = $website->pages()->count() === 0;

        $page = $website->pages()->create([
            'title' => $data['title'],
            'slug' => $isFirstPage ? null : $this->generateUniqueSlug($website, $data['title']),
            'is_home' => $isFirstPage,
            'meta_description' => $data['meta_description'] ?? null,
            'order' => ($website->pages()->max('order') ?? -1) + 1,
        ]);

        return $page;
    }

    public function update(WebsitePage $page, array $data): WebsitePage
    {
        if (! empty($data['is_home'])) {
            $page->website->pages()->where('id', '!=', $page->id)->update(['is_home' => false]);
            $data['slug'] = null;
        }

        $page->update($data);

        return $page;
    }

    public function delete(WebsitePage $page): void
    {
        if ($page->is_home && $page->website->pages()->count() === 1) {
            throw new InvalidArgumentException('A website must have at least one page.');
        }

        $wasHome = $page->is_home;
        $website = $page->website;

        $page->delete();

        if ($wasHome) {
            $website->pages()->orderBy('order')->first()?->update(['is_home' => true, 'slug' => null]);
        }
    }

    /**
     * Batch reorder pages. $items is [{id, order}, ...].
     *
     * @param  array<int, array{id: int, order: int}>  $items
     */
    public function reorder(Website $website, array $items): void
    {
        $this->reorderBatch($website->pages(), $items);
    }

    public function moveUp(WebsitePage $page): void
    {
        $this->moveOrderUp($page, 'website_id');
    }

    public function moveDown(WebsitePage $page): void
    {
        $this->moveOrderDown($page, 'website_id');
    }

    private function generateUniqueSlug(Website $website, string $title): string
    {
        return $this->uniqueSlug($title, fn ($slug) => $website->pages()->where('slug', $slug)->exists());
    }
}
