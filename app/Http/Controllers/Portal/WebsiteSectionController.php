<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Concerns\AssertsWebsiteOwnership;
use App\Http\Requests\Portal\StoreWebsiteSectionRequest;
use App\Http\Requests\Portal\UpdateWebsiteSectionRequest;
use App\Models\SectionType;
use App\Models\WebsitePage;
use App\Models\WebsiteSection;
use App\Services\WebsiteSectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebsiteSectionController extends Controller
{
    use AssertsWebsiteOwnership;

    public function __construct(private readonly WebsiteSectionService $sections)
    {
    }

    public function store(StoreWebsiteSectionRequest $request, WebsitePage $page): RedirectResponse
    {
        $this->assertOwnedByClient($request, $page);

        $sectionType = SectionType::query()->findOrFail($request->validated('section_type_id'));
        $style = $request->validated('style') ?? array_key_first($sectionType->contract()->availableStyles());

        $this->sections->create($page, $sectionType, $sectionType->contract()->defaultContent(), $style);

        return back()->with('status', 'Section added.');
    }

    public function update(UpdateWebsiteSectionRequest $request, WebsiteSection $section): RedirectResponse
    {
        $this->assertBelongsToPage($request, $section);

        $style = $request->validated('style') ?? ($section->settings['style'] ?? 'default');
        $this->sections->update($section, $request->validated('content'), $style);

        return back()->with('status', 'Section updated.');
    }

    public function destroy(Request $request, WebsiteSection $section): RedirectResponse
    {
        $this->assertBelongsToPage($request, $section);

        $this->sections->delete($section);

        return back()->with('status', 'Section removed.');
    }

    public function duplicate(Request $request, WebsiteSection $section): RedirectResponse
    {
        $this->assertBelongsToPage($request, $section);

        $this->sections->duplicate($section);

        return back()->with('status', 'Section duplicated.');
    }

    public function reorder(Request $request, WebsitePage $page): JsonResponse
    {
        $this->assertOwnedByClient($request, $page);

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.order' => ['required', 'integer', 'min:0'],
        ]);

        $this->sections->reorder($page, $validated['items']);

        return response()->json(['ok' => true]);
    }
}
