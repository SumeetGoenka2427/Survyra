<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\UpdateWebsiteThemeRequest;
use App\Services\WebsiteThemeService;
use Illuminate\Http\RedirectResponse;

class WebsiteThemeController extends Controller
{
    public function __construct(private readonly WebsiteThemeService $themes)
    {
    }

    public function update(UpdateWebsiteThemeRequest $request): RedirectResponse
    {
        $website = $request->user()->client->websites()->firstOrFail();
        $data = $request->validated();

        if (! empty($data['theme_id'])) {
            $theme = $this->themes->findSelectable($website->client, (int) $data['theme_id']);

            $this->themes->selectTheme($website, $theme);
        } else {
            $this->themes->updateFields($website, collect($data)->except('theme_id')->filter(fn ($v) => $v !== null)->all());
        }

        return redirect()->route('portal.website.edit')->with('status', 'Theme updated.');
    }
}
