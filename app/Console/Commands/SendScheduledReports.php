<?php

namespace App\Console\Commands;

use App\Mail\ScheduledReportMail;
use App\Services\ReportExportService;
use App\Services\ReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendScheduledReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:send-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and email every scheduled report that is due';

    public function handle(ReportService $reports, ReportExportService $exporter): void
    {
        $due = $reports->dueReports();

        foreach ($due as $report) {
            $from = match ($report->frequency) {
                'weekly' => now()->subWeek(),
                'monthly' => now()->subMonthNoOverflow(),
                'quarterly' => now()->subMonthsNoOverflow(3),
                default => now()->subWeek(),
            };
            $to = now();

            [$contents, $fileName, $mime] = match ($report->type) {
                'excel' => [$exporter->toExcel($report->client, $report->survey, $from, $to), 'report.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
                'csv' => [$exporter->toCsv($report->client, $report->survey, $from, $to), 'report.csv', 'text/csv'],
                default => [$exporter->toPdf($report->client, $report->survey, $from, $to), 'report.pdf', 'application/pdf'],
            };

            foreach ($report->recipients as $address) {
                Mail::to($address)->send(new ScheduledReportMail($report->client, $report, $contents, $fileName, $mime));
            }

            $reports->advanceSchedule($report);

            $this->info("Sent {$report->type} report #{$report->id} to ".count($report->recipients).' recipient(s).');
        }

        if ($due->isEmpty()) {
            $this->info('No scheduled reports are due.');
        }
    }
}
