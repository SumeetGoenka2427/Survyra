<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\AnalyticsService;
use App\Services\ReportExportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientAnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analytics,
        private readonly ReportExportService $exporter,
    ) {
    }

    public function show(Request $request, Client $client): View
    {
        $this->authorize('view', $client);

        [$from, $to] = $this->resolveRange($request);
        $data = $this->analytics->forClientDashboard($client, $from, $to);

        return view('admin.clients.analytics.index', [
            'client' => $client,
            'data' => $data,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function data(Request $request, Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        [$from, $to] = $this->resolveRange($request);
        $data = $this->analytics->forClientDashboard($client, $from, $to);

        return response()->json($data);
    }

    public function export(Request $request, Client $client, string $format): StreamedResponse
    {
        $this->authorize('view', $client);

        abort_unless(in_array($format, ['pdf', 'excel', 'csv'], true), 404);

        [$from, $to] = $this->resolveRange($request);

        $contents = match ($format) {
            'excel' => $this->exporter->toExcel($client, null, $from, $to),
            'csv' => $this->exporter->toCsv($client, null, $from, $to),
            default => $this->exporter->toPdf($client, null, $from, $to),
        };

        $extension = $format === 'excel' ? 'xlsx' : $format;
        $fileName = "survyra-client-{$client->id}-{$from->toDateString()}-{$to->toDateString()}.{$extension}";
        $mime = match ($format) {
            'excel' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
            default => 'application/pdf',
        };

        return response()->streamDownload(function () use ($contents) {
            echo $contents;
        }, $fileName, ['Content-Type' => $mime]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveRange(Request $request): array
    {
        $to = $request->filled('to') ? Carbon::parse($request->string('to')->toString())->endOfDay() : now()->endOfDay();
        $from = $request->filled('from') ? Carbon::parse($request->string('from')->toString())->startOfDay() : $to->copy()->subDays(30)->startOfDay();

        return [$from, $to];
    }
}