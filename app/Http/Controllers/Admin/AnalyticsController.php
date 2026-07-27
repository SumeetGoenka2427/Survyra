<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Survey;
use App\Services\AnalyticsService;
use App\Services\RealtimeAnalyticsService;
use App\Services\ReportExportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analytics,
        private readonly RealtimeAnalyticsService $realtime,
        private readonly ReportExportService $exporter,
    ) {
    }

    public function index(Request $request): View
    {
        $clients = Client::query()->orderBy('company_name')->get();
        $client = $this->resolveClient($request, $clients);
        [$survey, $surveys] = $this->resolveSurvey($request, $client);
        [$from, $to] = $this->resolveRange($request);

        return view('admin.analytics.index', [
            'clients' => $clients,
            'client' => $client,
            'surveys' => $surveys,
            'survey' => $survey,
            'from' => $from,
            'to' => $to,
            'snapshot' => $client ? $this->analytics->forClient($client, $survey, $from, $to) : null,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $client = $this->resolveClient($request, Client::query()->get());
        [$survey] = $this->resolveSurvey($request, $client);
        [$from, $to] = $this->resolveRange($request);

        $snapshot = $client ? $this->analytics->forClient($client, $survey, $from, $to) : null;

        return response()->json([
            'html' => $snapshot ? view('analytics.dashboard', ['snapshot' => $snapshot, 'survey' => $survey])->render() : '',
            'chart' => $snapshot ? [
                'trend' => $snapshot['trend'],
                'weekly_trend' => $snapshot['weekly_trend'],
                'sentiment' => $snapshot['sentiment_counts'],
            ] : null,
        ]);
    }

    public function poll(Request $request, Survey $survey): JsonResponse
    {
        $lastCount = $request->integer('last_count', 0);
        $data = $this->realtime->poll($survey, $lastCount);

        return response()->json($data);
    }

    public function export(Request $request, string $format): StreamedResponse
    {
        abort_unless(in_array($format, ['pdf', 'excel', 'csv'], true), 404);

        $client = $this->resolveClient($request, Client::query()->get());
        [$survey] = $this->resolveSurvey($request, $client);
        [$from, $to] = $this->resolveRange($request);

        abort_if(! $client, 404);

        $contents = match ($format) {
            'excel' => $this->exporter->toExcel($client, $survey, $from, $to),
            'csv' => $this->exporter->toCsv($client, $survey, $from, $to),
            default => $this->exporter->toPdf($client, $survey, $from, $to),
        };

        $extension = $format === 'excel' ? 'xlsx' : $format;
        $fileName = "survyra-report-{$client->id}-{$from->toDateString()}-{$to->toDateString()}.{$extension}";
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
     * @param  \Illuminate\Support\Collection<int, Client>  $clients
     */
    private function resolveClient(Request $request, $clients): ?Client
    {
        if ($request->integer('client_id')) {
            return $clients->firstWhere('id', $request->integer('client_id')) ?? Client::query()->find($request->integer('client_id'));
        }

        return $clients->first();
    }

    /**
     * @return array{0: ?Survey, 1: \Illuminate\Database\Eloquent\Collection<int, Survey>}
     */
    private function resolveSurvey(Request $request, ?Client $client): array
    {
        if (! $client) {
            return [null, collect()];
        }

        $surveys = $client->surveys()->orderBy('title')->get();
        $survey = $request->integer('survey_id') ? $surveys->firstWhere('id', $request->integer('survey_id')) : null;

        return [$survey, $surveys];
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
