<?php

namespace App\Services;

use App\Models\Response;
use App\Models\Survey;
use Illuminate\Support\Facades\Cache;

class RealtimeAnalyticsService
{
    private const POLLING_CACHE_TTL = 5; // seconds
    private const COUNTERS_CACHE_TTL = 60; // seconds

    /**
     * Get the latest analytics snapshot for polling.
     *
     * @return array<string, mixed>
     */
    public function poll(Survey $survey, int $lastKnownCount = 0): array
    {
        $cacheKey = "realtime:{$survey->id}";
        $lastCheckKey = "realtime:{$survey->id}:last_check";

        $totalResponses = $survey->responses()->where('status', 'completed')->count();
        $startedResponses = $survey->responses()->count();

        // Incremental update only
        $newResponses = $totalResponses - $lastKnownCount;
        $hasUpdates = $newResponses > 0;

        if ($hasUpdates) {
            $recentResponses = $survey->responses()
                ->where('status', 'completed')
                ->latest('completed_at')
                ->take($newResponses > 5 ? 5 : $newResponses)
                ->get(['id', 'score', 'sentiment', 'completed_at'])
                ->map(fn ($r) => [
                    'score' => $r->score,
                    'sentiment' => $r->sentiment,
                    'completed_at' => $r->completed_at?->diffForHumans(),
                ]);
        }

        $lastCheck = now()->timestamp;

        return [
            'total_responses' => $totalResponses,
            'started_responses' => $startedResponses,
            'new_responses' => $newResponses,
            'has_updates' => $hasUpdates,
            'recent' => $recentResponses ?? [],
            'last_check' => $lastCheck,
            'server_time' => now()->toIso8601String(),
        ];
    }

    /**
     * Get live counters for the analytics dashboard.
     *
     * @return array<string, mixed>
     */
    public function liveCounters(int $clientId): array
    {
        $cacheKey = "realtime:counters:{$clientId}";

        return Cache::remember($cacheKey, self::COUNTERS_CACHE_TTL, function () use ($clientId) {
            $today = now()->startOfDay();
            $lastHour = now()->subHour();

            $responsesToday = Response::whereHas('survey', fn ($q) => $q->where('client_id', $clientId))
                ->where('status', 'completed')
                ->where('completed_at', '>=', $today)
                ->count();

            $responsesLastHour = Response::whereHas('survey', fn ($q) => $q->where('client_id', $clientId))
                ->where('status', 'completed')
                ->where('completed_at', '>=', $lastHour)
                ->count();

            return [
                'today' => $responsesToday,
                'last_hour' => $responsesLastHour,
            ];
        });
    }

    /**
     * Invalidate polling cache for a survey.
     */
    public function invalidate(Survey $survey): void
    {
        Cache::forget("realtime:{$survey->id}");
        Cache::forget("realtime:{$survey->id}:last_check");
    }
}