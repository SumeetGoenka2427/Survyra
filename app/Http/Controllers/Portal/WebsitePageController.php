<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Concerns\AssertsWebsiteOwnership;
use App\Http\Requests\Portal\StoreWebsitePageRequest;
use App\Http\Requests\Portal\UpdateWebsitePageRequest;
use App\Models\WebsitePage;
use App\Services\WebsitePageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebsitePageController extends Controller
{
    use AssertsWebsiteOwnership;

    public function __construct(private readonly WebsitePageService $pages)
    {
    }

    public function store(StoreWebsitePageRequest $request): RedirectResponse
    {
        $website = $request->user()->client->websites()->firstOrFail();

        $this->pages->create($website, $request->validated());

        return back()->with('status', 'Page added.');
    }

    public function update(UpdateWebsitePageRequest $request, WebsitePage $page): RedirectResponse
    {
        $this->assertOwnedByClient($request, $page);

        $this->pages->update($page, $request->validated());

        return back()->with('status', 'Page updated.');
    }

    public function destroy(Request $request, WebsitePage $page): RedirectResponse
    {
        $this->assertOwnedByClient($request, $page);

        $this->pages->delete($page);

        return redirect()->route('portal.website.edit')->with('status', 'Page removed.');
    }

    public function reorder(Request $request): JsonResponse
    {
        $website = $request->user()->client->websites()->firstOrFail();

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.order' => ['required', 'integer', 'min:0'],
        ]);

        $this->pages->reorder($website, $validated['items']);

        return response()->json(['ok' => true]);
    }
}
