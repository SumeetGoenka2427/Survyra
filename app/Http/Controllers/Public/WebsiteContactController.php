<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWebsiteContactRequest;
use App\Models\Website;
use App\Models\WebsiteLead;
use App\Models\WebsiteSection;
use Illuminate\Http\RedirectResponse;

class WebsiteContactController extends Controller
{
    public function store(StoreWebsiteContactRequest $request, string $slug): RedirectResponse
    {
        $website = Website::query()->where('slug', $slug)->where('status', 'published')->firstOrFail();
        $section = WebsiteSection::query()->findOrFail($request->validated('section_id'));

        abort_if($section->page->website_id !== $website->id, 404);

        $data = collect($request->validated())->except(['section_id', 'page_id', 'company_website'])->all();

        WebsiteLead::query()->create([
            'client_id' => $website->client_id,
            'website_id' => $website->id,
            'website_page_id' => $request->validated('page_id'),
            'section_id' => $section->id,
            'data' => $data,
            'status' => 'new',
        ]);

        return back()->with('website_contact_sent', true);
    }
}
