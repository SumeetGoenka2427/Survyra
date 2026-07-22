<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyTemplateRequest;
use App\Http\Requests\UpdateSurveyTemplateRequest;
use App\Models\QuestionType;
use App\Models\SurveyTemplate;
use App\Services\SurveyTemplateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SurveyTemplateController extends Controller
{
    public function __construct(private readonly SurveyTemplateService $templates)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SurveyTemplate::class);

        return view('admin.templates.index', [
            'templatesByIndustry' => $this->templates->allGroupedByIndustry($this->searchTerm($request)),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SurveyTemplate::class);

        return response()->json(['html' => $this->renderFragment($request)]);
    }

    public function create(): View
    {
        $this->authorize('create', SurveyTemplate::class);

        return view('admin.templates.create');
    }

    public function store(StoreSurveyTemplateRequest $request): RedirectResponse
    {
        $template = $this->templates->create($request->validated(), $request->user()->id);

        return redirect()->route('admin.templates.edit', $template)
            ->with('status', 'Template created. Now add its questions below.');
    }

    public function edit(SurveyTemplate $template): View
    {
        $this->authorize('update', $template);

        return view('admin.templates.edit', [
            'template' => $this->templates->find($template->id),
            'questionTypes' => QuestionType::query()->where('is_active', true)->orderBy('label')->get(),
        ]);
    }

    public function update(UpdateSurveyTemplateRequest $request, SurveyTemplate $template): RedirectResponse
    {
        $this->templates->update($template, $request->validated());

        return redirect()->route('admin.templates.edit', $template)
            ->with('status', 'Template updated.');
    }

    public function destroy(SurveyTemplate $template, Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $template);

        $this->templates->delete($template);

        if ($request->wantsJson()) {
            return response()->json(['html' => $this->renderFragment($request)]);
        }

        return redirect()->route('admin.templates.index')->with('status', 'Template removed.');
    }

    public function duplicate(SurveyTemplate $template, Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', SurveyTemplate::class);

        $copy = $this->templates->duplicate($template, $request->user()->id);

        if ($request->wantsJson()) {
            return response()->json(['html' => $this->renderFragment($request), 'editUrl' => route('admin.templates.edit', $copy)]);
        }

        return redirect()->route('admin.templates.edit', $copy)
            ->with('status', 'Template duplicated as "'.$copy->name.'".');
    }

    private function renderFragment(Request $request): string
    {
        return view('admin.templates._fragment', [
            'templatesByIndustry' => $this->templates->allGroupedByIndustry($this->searchTerm($request)),
        ])->render();
    }

    private function searchTerm(Request $request): ?string
    {
        return $request->string('search')->toString() ?: null;
    }
}
