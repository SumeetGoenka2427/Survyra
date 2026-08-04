<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\StoreWebsiteRequest;
use App\Http\Requests\Portal\UpdateWebsiteRequest;
use App\Models\SectionType;
use App\Models\Website;
use App\Models\WebsiteTemplate;
use App\Services\UsageService;
use App\Services\WebsiteService;
use App\Services\WebsiteThemeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    public function __construct(
        private readonly WebsiteService $websites,
        private readonly UsageService $usage,
        private readonly WebsiteThemeService $themes,
    ) {
    }

    public function edit(Request $request): View
    {
        $client = $request->user()->client;
        $website = $client->websites()->with(['theme', 'pages.sections.sectionType'])->first();

        return view('portal.website.edit', [
            'website' => $website,
            'themes' => $this->themes->selectableFor($client),
            'sectionTypes' => SectionType::query()->where('is_active', true)->get(),
            'canCreateWebsite' => $this->usage->canCreateWebsite($client),
            'templates' => WebsiteTemplate::query()->where('is_active', true)->orderBy('industry')->get(),
        ]);
    }

    public function store(StoreWebsiteRequest $request): RedirectResponse
    {
        $client = $request->user()->client;

        abort_unless($this->usage->canCreateWebsite($client), 403, 'Your plan does not allow another website.');

        $data = $request->validated();

        if (! empty($data['template_id'])) {
            $template = WebsiteTemplate::query()->where('is_active', true)->findOrFail($data['template_id']);
            $this->websites->createFromTemplate($client, $template, $data['name'], $request->user()->id);
        } else {
            $this->websites->create($client, $data, $request->user()->id);
        }

        return redirect()->route('portal.website.edit')->with('status', 'Website created.');
    }

    public function update(UpdateWebsiteRequest $request): RedirectResponse
    {
        $this->websites->update($this->resolveWebsite($request), $request->validated());

        return redirect()->route('portal.website.edit')->with('status', 'Website updated.');
    }

    public function publish(Request $request): RedirectResponse
    {
        $this->websites->publish($this->resolveWebsite($request));

        return redirect()->route('portal.website.edit')->with('status', 'Website published.');
    }

    public function unpublish(Request $request): RedirectResponse
    {
        $this->websites->unpublish($this->resolveWebsite($request));

        return redirect()->route('portal.website.edit')->with('status', 'Website unpublished.');
    }

    private function resolveWebsite(Request $request): Website
    {
        return $request->user()->client->websites()->firstOrFail();
    }
}
