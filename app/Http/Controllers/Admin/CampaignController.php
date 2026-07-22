<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCampaignRequest;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\Survey;
use App\Services\CampaignService;
use App\Services\UsageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function __construct(
        private readonly CampaignService $campaigns,
        private readonly UsageService $usage,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Campaign::class);

        return view('admin.campaigns.index', [
            'clients' => Client::query()->orderBy('company_name')->get(),
            'selectedClient' => $this->selectedClient($request),
            'campaigns' => $this->paginatedCampaigns($request),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Campaign::class);

        return response()->json(['html' => $this->renderFragment($request)]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Campaign::class);

        $client = $request->integer('client_id') ? Client::query()->find($request->integer('client_id')) : null;

        return view('admin.campaigns.create', [
            'clients' => Client::query()->where('status', '!=', 'inactive')->orderBy('company_name')->get(),
            'selectedClient' => $client,
            'surveys' => $client ? Survey::query()->where('client_id', $client->id)->where('status', 'published')->get() : collect(),
            'tags' => $client ? $client->contactTags()->orderBy('name')->get() : collect(),
        ]);
    }

    public function store(StoreCampaignRequest $request): RedirectResponse
    {
        $client = Client::query()->findOrFail($request->validated('client_id'));
        $survey = Survey::query()->findOrFail($request->validated('survey_id'));

        $result = $this->campaigns->createWithRecipients(
            $client,
            $survey,
            $request->safe()->only(['name', 'type', 'message_template']),
            $request->validated('tag_ids', []),
            $request->user()->id
        );

        $status = "Campaign created with {$result['campaign']->recipients()->count()} recipient(s).";
        if ($result['excluded_for_consent'] > 0) {
            $status .= " {$result['excluded_for_consent']} contact(s) excluded (no consent on file).";
        }

        return redirect()->route('admin.campaigns.show', $result['campaign'])->with('status', $status);
    }

    public function show(Campaign $campaign): View
    {
        $this->authorize('view', $campaign);

        return view('admin.campaigns.show', ['campaign' => $this->campaigns->find($campaign->id)]);
    }

    public function send(Campaign $campaign, Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $campaign);

        if (! $this->usage->canSendCampaign($campaign->client)) {
            $message = 'Monthly campaign send limit reached for this client\'s subscription plan.';
            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }
            return back()->withErrors(['campaign' => $message]);
        }

        $this->campaigns->send($campaign);

        if ($request->wantsJson()) {
            return response()->json(['html' => $this->renderFragment($request)]);
        }

        return back()->with('status', 'Campaign is sending.');
    }

    public function retry(Campaign $campaign, Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $campaign);

        $this->campaigns->retryFailed($campaign);

        if ($request->wantsJson()) {
            return response()->json(['html' => $this->renderFragment($request)]);
        }

        return back()->with('status', 'Retrying failed recipients.');
    }

    private function selectedClient(Request $request): ?Client
    {
        return $request->integer('client_id') ? Client::query()->find($request->integer('client_id')) : null;
    }

    private function paginatedCampaigns(Request $request)
    {
        $client = $this->selectedClient($request);

        return $client
            ? $this->campaigns->paginate($client)
            : Campaign::query()->with('client', 'survey')->withCount('recipients')->latest()->paginate(15)->withQueryString();
    }

    private function renderFragment(Request $request): string
    {
        return view('admin.campaigns._fragment', [
            'campaigns' => $this->paginatedCampaigns($request),
        ])->render();
    }
}
