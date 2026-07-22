<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class ReportService
{
    public function paginate(Client $client, int $perPage = 15): LengthAwarePaginator
    {
        return $client->reports()->with('survey')->latest()->paginate($perPage)->withQueryString();
    }

    public function create(Client $client, array $data, ?int $createdByUserId): Report
    {
        return $client->reports()->create([
            ...$data,
            'next_run_at' => $this->nextRunFor($data['frequency'], now()),
            'created_by' => $createdByUserId,
        ]);
    }

    public function delete(Report $report): void
    {
        $report->delete();
    }

    /**
     * @return Collection<int, Report>
     */
    public function dueReports(): Collection
    {
        return Report::query()
            ->where('is_active', true)
            ->where('next_run_at', '<=', now())
            ->with('client', 'survey')
            ->get();
    }

    public function advanceSchedule(Report $report): void
    {
        $report->update([
            'last_sent_at' => now(),
            'next_run_at' => $this->nextRunFor($report->frequency, now()),
        ]);
    }

    private function nextRunFor(string $frequency, Carbon $from): Carbon
    {
        return match ($frequency) {
            'weekly' => $from->copy()->addWeek(),
            'monthly' => $from->copy()->addMonthNoOverflow(),
            'quarterly' => $from->copy()->addMonthsNoOverflow(3),
            default => $from->copy()->addWeek(),
        };
    }
}
