<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Survey;
use App\Services\AnalyticsService;
use App\Services\OnboardingService;
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
        private readonly ReportExportService $exporter,
        private readonly OnboardingService $onboarding,
    ) {}

    public function index(Request $request): View
    {
        $client = $request->user()->client;
        [$survey, $surveys] = $this->resolveSurvey($request, $client);
        [$from, $to] = $this->resolveRange($request);

        return view('portal.analytics.index', [
            'client' => $client,
            'surveys' => $surveys,
            'survey' => $survey,
            'from' => $from,
            'to' => $to,
            'snapshot' => $this->analytics->forClient($client, $survey, $from, $to),
            'onboarding' => $this->onboarding->getChecklist($request->user()),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $client = $request->user()->client;
        [$survey] = $this->resolveSurvey($request, $client);
        [$from, $to] = $this->resolveRange($request);

        $snapshot = $this->analytics->forClient($client, $survey, $from, $to);

        return response()->json([
            'html' => view('analytics.dashboard', ['snapshot' => $snapshot])->render(),
            'chart' => [
                'trend' => $snapshot['trend'],
                'sentiment' => $snapshot['sentiment_counts'],
            ],
        ]);
    }

    public function export(Request $request, string $format): StreamedResponse
    {
        abort_unless(in_array($format, ['pdf', 'excel', 'csv'], true), 404);

        $client = $request->user()->client;
        [$survey] = $this->resolveSurvey($request, $client);
        [$from, $to] = $this->resolveRange($request);

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
     * @return array{0: ?Survey, 1: \Illuminate\Database\Eloquent\Collection<int, Survey>}
     */
    private function resolveSurvey(Request $request, Client $client): array
    {
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
