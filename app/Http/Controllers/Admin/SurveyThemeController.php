<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyThemeRequest;
use App\Http\Requests\UpdateSurveyThemeRequest;
use App\Models\SurveyTheme;
use App\Services\SurveyThemeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SurveyThemeController extends Controller
{
    public function __construct(private readonly SurveyThemeService $themes)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SurveyTheme::class);

        return view('admin.themes.index', [
            'themes' => $this->themes->all($this->searchTerm($request)),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SurveyTheme::class);

        return response()->json(['html' => $this->renderFragment($request)]);
    }

    public function create(): View
    {
        $this->authorize('create', SurveyTheme::class);

        return view('admin.themes.create');
    }

    public function store(StoreSurveyThemeRequest $request): RedirectResponse
    {
        $this->themes->create($request->validated());

        return redirect()->route('admin.themes.index')->with('status', 'Theme created.');
    }

    public function edit(SurveyTheme $theme): View
    {
        $this->authorize('update', $theme);

        return view('admin.themes.edit', ['theme' => $theme]);
    }

    public function update(UpdateSurveyThemeRequest $request, SurveyTheme $theme): RedirectResponse
    {
        $this->themes->update($theme, $request->validated());

        return redirect()->route('admin.themes.index')->with('status', 'Theme updated.');
    }

    public function destroy(SurveyTheme $theme, Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $theme);

        $this->themes->delete($theme);

        if ($request->wantsJson()) {
            return response()->json(['html' => $this->renderFragment($request)]);
        }

        return redirect()->route('admin.themes.index')->with('status', 'Theme removed.');
    }

    private function renderFragment(Request $request): string
    {
        return view('admin.themes._fragment', [
            'themes' => $this->themes->all($this->searchTerm($request)),
        ])->render();
    }

    private function searchTerm(Request $request): ?string
    {
        return $request->string('search')->toString() ?: null;
    }
}
