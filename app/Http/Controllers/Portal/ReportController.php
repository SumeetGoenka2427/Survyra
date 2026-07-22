<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\StoreReportRequest;
use App\Models\Client;
use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(['html' => $this->renderPanel($request->user()->client)]);
    }

    public function store(StoreReportRequest $request): JsonResponse
    {
        $this->reports->create(
            $request->user()->client,
            $request->safe()->only(['survey_id', 'type', 'frequency', 'recipients']),
            null
        );

        return response()->json(['html' => $this->renderPanel($request->user()->client)]);
    }

    public function destroy(Request $request, Report $report): JsonResponse
    {
        abort_unless($report->client_id === $request->user()->client_id, 404);

        $this->reports->delete($report);

        return response()->json(['html' => $this->renderPanel($request->user()->client)]);
    }

    private function renderPanel(Client $client): string
    {
        return view('analytics.reports-panel', [
            'reports' => $this->reports->paginate($client, 50),
            'surveys' => $client->surveys()->orderBy('title')->get(),
            'storeUrl' => route('portal.analytics.reports.store'),
            'deleteUrlTemplate' => route('portal.analytics.reports.destroy', ['report' => '__ID__']),
        ])->render();
    }
}
