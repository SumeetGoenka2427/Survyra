<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Website;
use App\Models\WebsiteTheme;
use Illuminate\Database\Eloquent\Collection;

class WebsiteThemeService
{
    /**
     * Themes a client may pick from: the shared system library plus any
     * theme they've customized (cloned) for themselves.
     */
    public function selectableFor(Client $client): Collection
    {
        return WebsiteTheme::query()->where('is_system', true)->orWhere('client_id', $client->id)->get();
    }

    /**
     * Resolves a theme_id the client is actually allowed to select (system
     * or their own), or fails - prevents selecting another client's theme.
     */
    public function findSelectable(Client $client, int $themeId): WebsiteTheme
    {
        return WebsiteTheme::query()->where('id', $themeId)
            ->where(fn ($q) => $q->where('is_system', true)->orWhere('client_id', $client->id))
            ->firstOrFail();
    }

    /**
     * Applies theme field changes to a website. If the website is still
     * using a shared system theme, clones it into a client-owned theme
     * first so the edit never mutates the shared library copy other
     * clients may also be using.
     */
    public function updateFields(Website $website, array $fields): WebsiteTheme
    {
        $theme = $website->theme;

        if (! $theme || $theme->is_system) {
            $theme = WebsiteTheme::query()->create(array_merge(
                $theme?->only([
                    'primary_color', 'secondary_color', 'background', 'heading_font',
                    'body_font', 'header_style', 'button_style', 'border_radius', 'container_width',
                ]) ?? [],
                ['name' => $website->name.' Theme', 'is_system' => false, 'client_id' => $website->client_id]
            ));

            $website->update(['theme_id' => $theme->id]);
        }

        $theme->update($fields);

        return $theme;
    }

    public function selectTheme(Website $website, WebsiteTheme $theme): Website
    {
        $website->update(['theme_id' => $theme->id]);

        return $website;
    }
}
