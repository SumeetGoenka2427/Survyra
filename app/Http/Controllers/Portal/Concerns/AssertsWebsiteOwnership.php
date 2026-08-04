<?php

namespace App\Http\Controllers\Portal\Concerns;

use App\Models\Website;
use App\Models\WebsitePage;
use App\Models\WebsiteSection;
use Illuminate\Http\Request;

/**
 * Website::class carries a global client-scope (BelongsToClient) that
 * transparently filters relation loads too - so ->website silently resolves
 * to null (not the real row) whenever the *authenticated* client isn't the
 * owner, which is exactly the case these checks need to catch. Query without
 * that scope to get the true owning client_id.
 */
trait AssertsWebsiteOwnership
{
    private function assertOwnedByClient(Request $request, WebsitePage $page): void
    {
        $clientId = Website::withoutGlobalScopes()->where('id', $page->website_id)->value('client_id');

        abort_if($clientId !== $request->user()->client_id, 403);
    }

    private function assertBelongsToPage(Request $request, WebsiteSection $section): void
    {
        $clientId = Website::withoutGlobalScopes()->where('id', $section->page->website_id)->value('client_id');

        abort_if($clientId !== $request->user()->client_id, 403);
    }
}
