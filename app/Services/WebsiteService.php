<?php

namespace App\Services;

use App\Models\Client;
use App\Models\SectionType;
use App\Models\Website;
use App\Models\WebsiteTemplate;
use App\Services\Concerns\GeneratesUniqueSlugs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WebsiteService
{
    use GeneratesUniqueSlugs;

    public function __construct(private readonly WebsiteSectionService $sections)
    {
    }

    public function create(Client $client, array $data, int $createdByUserId): Website
    {
        return DB::transaction(function () use ($client, $data, $createdByUserId) {
            $website = Website::query()->create([
                'client_id' => $client->id,
                'theme_id' => $data['theme_id'] ?? null,
                'name' => $data['name'],
                'slug' => $this->generateUniqueSlug($data['name']),
                'status' => 'draft',
                'created_by' => $createdByUserId,
            ]);

            $homePage = $website->pages()->create([
                'title' => 'Home',
                'slug' => null,
                'is_home' => true,
                'order' => 0,
            ]);

            $heroType = SectionType::query()->where('key', 'hero')->first();

            if ($heroType) {
                $homePage->sections()->create([
                    'section_type_id' => $heroType->id,
                    'content' => $heroType->contract()->defaultContent(),
                    'settings' => ['style' => 'centered'],
                    'order' => 0,
                ]);
            }

            return $website->fresh();
        });
    }

    /**
     * Materializes a WebsiteTemplate's JSON `structure` into a real Website
     * with real WebsitePage/WebsiteSection rows - built directly (not via
     * create(), which unconditionally seeds its own default Home+Hero and
     * would double-seed on top of the template). Each section's content is
     * routed through WebsiteSectionService::create() so it's validated
     * against the section type's CURRENT validationRules() exactly like a
     * live portal edit would be - a template authored against an older rule
     * set fails loudly here instead of silently producing broken content.
     */
    public function createFromTemplate(Client $client, WebsiteTemplate $template, string $name, int $createdByUserId): Website
    {
        return DB::transaction(function () use ($client, $template, $name, $createdByUserId) {
            $website = Website::query()->create([
                'client_id' => $client->id,
                'theme_id' => $template->theme_id,
                'name' => $name,
                'slug' => $this->generateUniqueSlug($name),
                'status' => 'draft',
                'created_by' => $createdByUserId,
            ]);

            foreach ($template->structure['pages'] ?? [] as $pageOrder => $pageDef) {
                $page = $website->pages()->create([
                    'title' => $pageDef['title'],
                    'slug' => ($pageDef['is_home'] ?? false) ? null : ($pageDef['slug'] ?? Str::slug($pageDef['title'])),
                    'is_home' => $pageDef['is_home'] ?? false,
                    'order' => $pageOrder,
                ]);

                foreach ($pageDef['sections'] ?? [] as $sectionDef) {
                    $sectionType = SectionType::query()->where('key', $sectionDef['type'])->firstOrFail();

                    $this->sections->create($page, $sectionType, $sectionDef['content'], $sectionDef['style'] ?? 'default');
                }
            }

            $template->increment('usage_count');

            return $website->fresh();
        });
    }

    public function update(Website $website, array $data): Website
    {
        $website->update($data);

        return $website;
    }

    public function publish(Website $website): Website
    {
        if ($website->pages()->count() === 0) {
            throw new InvalidArgumentException('A website needs at least one page before it can be published.');
        }

        $website->update([
            'published_snapshot' => $this->buildSnapshot($website, $this->publicUrlResolver($website)),
            'status' => 'published',
            'published_at' => now(),
        ]);

        return $website->fresh();
    }

    public function unpublish(Website $website): Website
    {
        $website->update(['status' => 'draft']);

        return $website;
    }

    /**
     * Ephemeral snapshot of the CURRENT LIVE DRAFT, shaped identically to the
     * persisted `published_snapshot` column, for the in-builder preview only.
     * NEVER persist, cache, or assign this return value to `published_snapshot`
     * - it reflects unpublished, possibly half-edited content. $pageUrlResolver
     * should point at preview routes, not public ones (see WebsitePreviewController).
     *
     * @return array<string, mixed>
     */
    public function previewSnapshot(Website $website, callable $pageUrlResolver): array
    {
        return $this->buildSnapshot($website, $pageUrlResolver);
    }

    /**
     * Materializes the live draft (pages/sections) into a plain nested array
     * for a renderer to read, so a half-edited draft is never visible to a
     * public visitor - the public side only ever reads the persisted
     * `published_snapshot`, never live builder tables directly.
     *
     * @return array<string, mixed>
     */
    private function buildSnapshot(Website $website, callable $pageUrlResolver): array
    {
        $website->load(['theme', 'pages.sections.sectionType']);

        return [
            'name' => $website->name,
            'meta_description' => $website->meta_description,
            'favicon_path' => $website->favicon_path,
            'og_image' => $website->og_image ?: $this->firstHeroImage($website),
            'social_links' => $website->social_links ?? [],
            'theme' => $website->theme?->only([
                'primary_color', 'secondary_color', 'background', 'heading_font',
                'body_font', 'header_style', 'button_style', 'border_radius',
                'container_width', 'custom_css',
            ]),
            'pages' => $website->pages->map(fn ($page) => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'is_home' => $page->is_home,
                'meta_description' => $page->meta_description,
                'sections' => $page->sections->map(fn ($section) => [
                    'id' => $section->id,
                    'type' => $section->sectionType->key,
                    'style' => $section->settings['style'] ?? 'default',
                    'content' => $this->resolveInternalLinks($website, $section->content ?? [], $pageUrlResolver),
                ])->all(),
            ])->all(),
        ];
    }

    private function firstHeroImage(Website $website): ?string
    {
        foreach ($website->pages as $page) {
            foreach ($page->sections as $section) {
                if ($section->sectionType->key === 'hero' && ! empty($section->content['background_image'])) {
                    return $section->content['background_image'];
                }
            }
        }

        return null;
    }

    /**
     * Section content stores internal links as {"type":"page","page_id":N}
     * rather than a baked path string, so retrofitting custom domains later
     * only means changing $pageUrlResolver - not rewriting stored content.
     */
    private function resolveInternalLinks(Website $website, array $content, callable $pageUrlResolver): array
    {
        foreach (['cta_link', 'button_link'] as $linkField) {
            if (! isset($content[$linkField]) || ! is_array($content[$linkField])) {
                continue;
            }

            $link = $content[$linkField];

            if (($link['type'] ?? null) === 'page' && isset($link['page_id'])) {
                $page = $website->pages->firstWhere('id', $link['page_id']);
                $content[$linkField]['url'] = $page ? $pageUrlResolver($page) : '#';
            } elseif (($link['type'] ?? null) === 'external') {
                $content[$linkField]['url'] = $link['url'] ?? '#';
            }
        }

        return $content;
    }

    private function publicUrlResolver(Website $website): \Closure
    {
        return fn ($page) => route('website.show'.($page->is_home ? '' : '.page'), array_filter([
            'slug' => $website->slug,
            'page' => $page->is_home ? null : $page->slug,
        ]));
    }

    private function generateUniqueSlug(string $name): string
    {
        return $this->uniqueSlug($name, fn ($slug) => Website::query()->withoutGlobalScopes()->where('slug', $slug)->exists());
    }
}
