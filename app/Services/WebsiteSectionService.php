<?php

namespace App\Services;

use App\Models\SectionType;
use App\Models\WebsitePage;
use App\Models\WebsiteSection;
use App\Services\Concerns\ReordersQuestions;
use Illuminate\Support\Facades\Validator;

class WebsiteSectionService
{
    use ReordersQuestions;

    public function create(WebsitePage $page, SectionType $sectionType, array $content, string $style): WebsiteSection
    {
        $this->validateContent($sectionType, $content);

        return $page->sections()->create([
            'section_type_id' => $sectionType->id,
            'content' => $content,
            'settings' => ['style' => $style],
            'order' => ($page->sections()->max('order') ?? -1) + 1,
        ]);
    }

    public function update(WebsiteSection $section, array $content, string $style): WebsiteSection
    {
        $this->validateContent($section->sectionType, $content);

        $section->update([
            'content' => $content,
            'settings' => array_merge($section->settings ?? [], ['style' => $style]),
        ]);

        return $section;
    }

    public function delete(WebsiteSection $section): void
    {
        $section->delete();
    }

    public function duplicate(WebsiteSection $section): WebsiteSection
    {
        return $section->page->sections()->create([
            'section_type_id' => $section->section_type_id,
            'content' => $section->content,
            'settings' => $section->settings,
            'order' => ($section->page->sections()->max('order') ?? -1) + 1,
        ]);
    }

    /**
     * Batch reorder sections within a page. $items is [{id, order}, ...].
     *
     * @param  array<int, array{id: int, order: int}>  $items
     */
    public function reorder(WebsitePage $page, array $items): void
    {
        $this->reorderBatch($page->sections(), $items);
    }

    public function moveUp(WebsiteSection $section): void
    {
        $this->moveOrderUp($section, 'page_id');
    }

    public function moveDown(WebsiteSection $section): void
    {
        $this->moveOrderDown($section, 'page_id');
    }

    private function validateContent(SectionType $sectionType, array $content): void
    {
        Validator::make($content, $sectionType->contract()->validationRules($content))->validate();
    }
}
