<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\WebsiteService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebsitePreviewController extends Controller
{
    public function __construct(private readonly WebsiteService $websites)
    {
    }

    /**
     * Renders the CURRENT LIVE DRAFT (not the published snapshot) inside the
     * portal builder's Preview tab iframe. Read-only, no client.can-edit gate,
     * and deliberately its own minimal shell (no OG/canonical/JSON-LD, explicit
     * noindex) - an authenticated preview route should never look indexable.
     */
    public function show(Request $request, ?string $page = null): Response
    {
        $website = $request->user()->client->websites()->firstOrFail();

        $snapshot = $this->websites->previewSnapshot($website, fn ($p) => route('portal.website.preview', array_filter([
            'page' => $p->is_home ? null : $p->slug,
        ])));

        $pages = collect($snapshot['pages'] ?? []);
        $pageData = $page === null
            ? $pages->firstWhere('is_home', true)
            : $pages->firstWhere('slug', $page);

        abort_if(! $pageData, 404);

        return response()->view('portal.website.preview', [
            'website' => $website,
            'snapshot' => $snapshot,
            'page' => $pageData,
        ]);
    }
}
