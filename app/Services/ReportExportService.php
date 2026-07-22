<?php

namespace App\Services;

use App\Exports\ResponsesExport;
use App\Models\Client;
use App\Models\Response;
use App\Models\Survey;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class ReportExportService
{
    public function __construct(private readonly AnalyticsService $analytics)
    {
    }

    public function toPdf(Client $client, ?Survey $survey, Carbon $from, Carbon $to): string
    {
        $html = view('pdf.report', [
            'client' => $client,
            'survey' => $survey,
            'snapshot' => $this->analytics->forClient($client, $survey, $from, $to),
            'responses' => $this->responses($client, $survey, $from, $to),
            'from' => $from,
            'to' => $to,
        ])->render();

        return Pdf::loadHTML($html)->output();
    }

    public function toExcel(Client $client, ?Survey $survey, Carbon $from, Carbon $to): string
    {
        return Excel::raw(new ResponsesExport($this->responses($client, $survey, $from, $to)), ExcelFormat::XLSX);
    }

    public function toCsv(Client $client, ?Survey $survey, Carbon $from, Carbon $to): string
    {
        return Excel::raw(new ResponsesExport($this->responses($client, $survey, $from, $to)), ExcelFormat::CSV);
    }

    private function responses(Client $client, ?Survey $survey, Carbon $from, Carbon $to): Collection
    {
        $surveyIds = $survey ? [$survey->id] : $client->surveys()->pluck('id');

        return Response::query()
            ->whereIn('survey_id', $surveyIds)
            ->whereBetween('started_at', [$from, $to])
            ->with(['survey', 'answers.question'])
            ->latest('started_at')
            ->get();
    }
}
