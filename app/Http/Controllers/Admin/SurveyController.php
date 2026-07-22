<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyRequest;
use App\Http\Requests\UpdateSurveyRequest;
use App\Models\Client;
use App\Models\QuestionType;
use App\Models\Survey;
use App\Models\SurveyTheme;
use App\Services\SurveyService;
use App\Services\SurveyTemplateService;
use App\Services\SurveyThemeService;
use App\Services\UsageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SurveyController extends Controller
{
    public function __construct(
        private readonly SurveyService $surveys,
        private readonly SurveyTemplateService $templates,
        private readonly SurveyThemeService $themes,
        private readonly UsageService $usage,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Survey::class);

        return view('admin.surveys.index', [
            'surveys' => $this->paginatedSurveys($request),
            'clients' => Client::query()->orderBy('company_name')->get(),
            'clientId' => $request->integer('client_id') ?: null,
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Survey::class);

        return response()->json(['html' => $this->renderFragment($request)]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Survey::class);

        return view('admin.surveys.create', [
            'clients' => Client::query()->where('status', '!=', 'inactive')->orderBy('company_name')->get(),
            'templatesByIndustry' => $this->templates->allGroupedByIndustry(),
            'selectedClientId' => $request->integer('client_id') ?: null,
        ]);
    }

    public function store(StoreSurveyRequest $request): RedirectResponse
    {
        $client = Client::query()->findOrFail($request->validated('client_id'));

        if ($request->validated('mode') === 'blank') {
            $survey = $this->surveys->createBlank(
                $client,
                $request->validated('title'),
                $request->validated('layout'),
                $request->user()->id
            );
        } else {
            $template = $this->templates->find($request->validated('survey_template_id'));
            $survey = $this->surveys->createFromTemplate($client, $template, $request->validated('title'), $request->user()->id);
        }

        return redirect()->route('admin.surveys.edit', $survey)
            ->with('status', 'Survey created. Customize it below.');
    }

    public function edit(Survey $survey): View
    {
        $this->authorize('update', $survey);

        return view('admin.surveys.edit', [
            'survey' => $this->surveys->find($survey->id),
            'questionTypes' => QuestionType::query()->where('is_active', true)->orderBy('label')->get(),
            'themes' => $this->themes->availableFor($survey->client_id),
        ]);
    }

    public function update(UpdateSurveyRequest $request, Survey $survey): RedirectResponse
    {
        $data = $request->validated();

        // Normalize boolean checkboxes that may not be sent when unchecked
        $data['is_anonymous'] = (bool) ($data['is_anonymous'] ?? false);
        $data['gdpr_enabled'] = (bool) ($data['gdpr_enabled'] ?? false);

        $this->surveys->update($survey, $data);

        return redirect()->route('admin.surveys.edit', $survey)->with('status', 'Survey updated.');
    }

    public function destroy(Survey $survey, Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $survey);

        try {
            $this->surveys->delete($survey);
        } catch (InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['survey' => $e->getMessage()]);
        }

        if ($request->wantsJson()) {
            return response()->json(['html' => $this->renderFragment($request)]);
        }

        return redirect()->route('admin.surveys.index')->with('status', 'Survey removed.');
    }

    public function duplicate(Survey $survey): RedirectResponse
    {
        $this->authorize('create', Survey::class);

        $copy = $this->surveys->duplicate($survey, request()->user()->id);

        return redirect()->route('admin.surveys.edit', $copy)
            ->with('status', 'Survey duplicated. You are now editing the copy.');
    }

    public function publish(Survey $survey, Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $survey);

        if (! $this->usage->canCreateSurvey($survey->client)) {
            $message = 'Active survey limit reached for this client\'s subscription plan.';
            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }
            return back()->withErrors(['survey' => $message]);
        }

        $this->surveys->publish($survey);

        if ($request->wantsJson()) {
            return response()->json(['html' => $this->renderFragment($request)]);
        }

        return back()->with('status', 'Survey published! Public link and QR code are ready below.');
    }

    public function archive(Survey $survey, Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $survey);

        $this->surveys->archive($survey);

        if ($request->wantsJson()) {
            return response()->json(['html' => $this->renderFragment($request)]);
        }

        return back()->with('status', 'Survey archived.');
    }

    public function duplicateTheme(Survey $survey, SurveyTheme $theme): RedirectResponse
    {
        $this->authorize('update', $survey);

        $copy = $this->themes->duplicateForClient($theme, $survey->client);
        $this->surveys->update($survey, ['theme_id' => $copy->id]);

        return redirect()->route('admin.themes.edit', $copy)
            ->with('status', 'Custom theme created for this client - tweak it below, then head back to the survey.');
    }

    public function downloadQr(Survey $survey): Response
    {
        $this->authorize('update', $survey);

        abort_unless($survey->status === 'published', 422, 'Only published surveys have a public link to encode.');

        $svg = QrCode::format('svg')->size(400)->generate(url("/s/{$survey->slug}"));

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }

    private function paginatedSurveys(Request $request)
    {
        return $this->surveys->paginate(
            15,
            $request->integer('client_id') ?: null,
            $request->string('status')->toString() ?: null
        );
    }

    private function renderFragment(Request $request): string
    {
        return view('admin.surveys._fragment', [
            'surveys' => $this->paginatedSurveys($request),
        ])->render();
    }
}
