<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Response;

class WebsiteController extends Controller
{
    public function show(string $slug, ?string $page = null): Response
    {
        $website = Website::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (! $website || ! $website->published_snapshot) {
            return response()->view('website.unavailable', [], 404);
        }

        $snapshot = $website->published_snapshot;
        $pages = collect($snapshot['pages'] ?? []);

        $pageData = $page === null
            ? $pages->firstWhere('is_home', true)
            : $pages->firstWhere('slug', $page);

        if (! $pageData) {
            return response()->view('website.unavailable', [], 404);
        }

        $canonicalUrl = $pageData['is_home']
            ? route('website.show', $website->slug)
            : route('website.show.page', [$website->slug, $pageData['slug']]);

        return response()->view('website.show', [
            'website' => $website,
            'snapshot' => $snapshot,
            'page' => $pageData,
            'canonicalUrl' => $canonicalUrl,
        ]);
    }
}
