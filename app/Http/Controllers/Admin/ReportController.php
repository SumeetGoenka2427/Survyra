<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
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
        $client = Client::query()->findOrFail($request->integer('client_id'));

        return response()->json(['html' => $this->renderPanel($client)]);
    }

    public function store(StoreReportRequest $request): JsonResponse
    {
        $client = Client::query()->findOrFail($request->validated('client_id'));

        $this->reports->create(
            $client,
            $request->safe()->only(['survey_id', 'type', 'frequency', 'recipients']),
            $request->user()->id
        );

        return response()->json(['html' => $this->renderPanel($client)]);
    }

    public function destroy(Report $report): JsonResponse
    {
        $client = $report->client;
        $this->reports->delete($report);

        return response()->json(['html' => $this->renderPanel($client)]);
    }

    private function renderPanel(Client $client): string
    {
        return view('analytics.reports-panel', [
            'reports' => $this->reports->paginate($client, 50),
            'surveys' => $client->surveys()->orderBy('title')->get(),
            'storeUrl' => route('admin.analytics.reports.store'),
            'deleteUrlTemplate' => route('admin.analytics.reports.destroy', ['report' => '__ID__']),
        ])->render();
    }
}
