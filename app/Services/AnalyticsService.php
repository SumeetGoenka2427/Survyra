<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Contact;
use App\Models\Response;
use App\Models\ResponseAnswer;
use App\Models\ReviewClick;
use App\Models\Survey;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * One computation engine shared by the admin and portal dashboards — the
 * only difference between the two surfaces is which client they're allowed
 * to pass in, enforced by the controllers/policies, not duplicated here.
 */
class AnalyticsService
{
    /**
     * Question type keys that make sense as a survey's "primary score question".
     */
    private const METRIC_KEYS = [
        'nps' => 'nps',
        'csat' => 'csat',
        'ces' => 'ces',
        'rating_stars' => 'rating',
        'emoji_rating' => 'rating',
    ];

    /**
     * @return array<string, mixed>
     */
    public function forClient(Client $client, ?Survey $survey, Carbon $from, Carbon $to): array
    {
        $cacheKey = "analytics:{$client->id}:" . ($survey?->id ?? 'all') . ":{$from->toDateString()}:{$to->toDateString()}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($client, $survey, $from, $to) {
            return $this->compute($client, $survey, $from, $to);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function forClientDashboard(Client $client, Carbon $from, Carbon $to): array
    {
        $cacheKey = "client-dashboard:{$client->id}:{$from->toDateString()}:{$to->toDateString()}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($client, $from, $to) {
            return $this->computeClientDashboard($client, $from, $to);
        });
    }

    public static function invalidateForClient(int $clientId): void
    {
        Cache::flush();
    }

    /**
     * Compute the survey analytics snapshot.
     */
    private function compute(Client $client, ?Survey $survey, Carbon $from, Carbon $to): array
    {
        $surveys = $survey ? collect([$survey]) : $client->surveys()->get();
        $surveyIds = $surveys->pluck('id');

        $base = fn () => Response::query()
            ->whereIn('survey_id', $surveyIds)
            ->whereBetween('started_at', [$from, $to]);

        $total = (clone $base())->count();
        $today = (clone $base())->whereDate('started_at', now()->toDateString())->count();
        $thisWeek = (clone $base())->whereBetween('started_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $completedQuery = fn () => (clone $base())->where('status', 'completed');
        $completedCount = (clone $completedQuery())->count();
        $abandonedCount = (clone $base())->where('status', 'abandoned')->count();
        $pendingCount = (clone $base())->whereNotIn('status', ['completed', 'abandoned'])->count();

        $avgSeconds = (clone $completedQuery())
            ->whereNotNull('completed_at')
            ->get(['started_at', 'completed_at'])
            ->map(fn (Response $r) => $r->completed_at->diffInSeconds($r->started_at, true))
            ->avg();

        // Previous period comparison
        $periodLength = $from->diffInDays($to);
        $prevFrom = $from->copy()->subDays($periodLength);
        $prevTo = $to->copy()->subDays($periodLength);
        $prevTotal = (clone $base())->whereBetween('started_at', [$prevFrom, $prevTo])->count();
        $growthRate = $prevTotal > 0 ? round((($total - $prevTotal) / $prevTotal) * 100, 1) : ($total > 0 ? 100 : 0);

        $sentimentCounts = (clone $completedQuery())
            ->selectRaw('sentiment, count(*) as count')
            ->whereNotNull('sentiment')
            ->groupBy('sentiment')
            ->pluck('count', 'sentiment');

        $devices = (clone $base())
            ->whereNotNull('device')
            ->selectRaw('device, count(*) as count')
            ->groupBy('device')
            ->pluck('count', 'device');

        $browsers = (clone $base())
            ->whereNotNull('browser')
            ->selectRaw('browser, count(*) as count')
            ->groupBy('browser')
            ->pluck('count', 'browser');

        $sources = (clone $base())
            ->whereNotNull('source')
            ->selectRaw('source, count(*) as count')
            ->groupBy('source')
            ->pluck('count', 'source');

        $countries = (clone $base())
            ->whereNotNull('country')
            ->selectRaw('country, count(*) as count')
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'country');

        // Daily trend with cumulative
        $trend = $this->trend($surveyIds, $from, $to);

        // Weekly trend for comparison
        $weeklyTrend = $this->weeklyTrend($surveyIds, $from, $to);

        // Hourly distribution
        $hourly = $this->hourlyDistribution($surveyIds, $from, $to);

        // Day of week distribution
        $dayOfWeek = $this->dayOfWeekDistribution($surveyIds, $from, $to);

        return [
            'total_responses' => $total,
            'today_responses' => $today,
            'this_week_responses' => $thisWeek,
            'completion_rate' => $total > 0 ? round($completedCount / $total * 100, 1) : 0.0,
            'abandonment_rate' => $total > 0 ? round($abandonedCount / $total * 100, 1) : 0.0,
            'pending_count' => $pendingCount,
            'avg_completion_seconds' => $avgSeconds ? (int) round($avgSeconds) : null,
            'growth_rate' => $growthRate,
            'sentiment_counts' => [
                'positive' => (int) ($sentimentCounts['positive'] ?? 0),
                'neutral' => (int) ($sentimentCounts['neutral'] ?? 0),
                'negative' => (int) ($sentimentCounts['negative'] ?? 0),
            ],
            'metrics' => $this->computeMetrics($surveys, $from, $to),
            'question_breakdown' => $survey ? $this->questionBreakdown($survey, $from, $to) : [],
            'recent_responses' => (clone $base())->with('survey')->latest('started_at')->limit(10)->get(),
            'positive_responses' => (clone $completedQuery())->where('sentiment', 'positive')->with('survey')->latest('completed_at')->limit(5)->get(),
            'negative_responses' => (clone $completedQuery())->where('sentiment', 'negative')->with('survey')->latest('completed_at')->limit(5)->get(),
            'trend' => $trend,
            'weekly_trend' => $weeklyTrend,
            'hourly_distribution' => $hourly,
            'day_of_week_distribution' => $dayOfWeek,
            'review_clicks' => $this->reviewClickBreakdown($client, $survey, $from, $to),
            'devices' => $devices,
            'browsers' => $browsers,
            'sources' => $sources,
            'countries' => $countries,
            'drop_off' => $survey ? $this->dropOffBreakdown($survey, $from, $to) : [],
            'survey_count' => $surveys->count(),
            'total_surveys' => $client->surveys()->count(),
            'active_surveys' => $client->surveys()->where('status', 'published')->count(),
            'completed_count' => $completedCount,
            'abandoned_count' => $abandonedCount,
            'avg_daily_responses' => $periodLength > 0 ? round($total / max($periodLength, 1), 1) : 0,
        ];
    }

    /**
     * Compute the client dashboard analytics.
     */
    private function computeClientDashboard(Client $client, Carbon $from, Carbon $to): array
    {
        $surveys = $client->surveys()->get();
        $surveyIds = $surveys->pluck('id');

        // Response stats
        $responseBase = Response::query()
            ->whereIn('survey_id', $surveyIds)
            ->whereBetween('started_at', [$from, $to]);

        $totalResponses = (clone $responseBase)->count();
        $completedResponses = (clone $responseBase)->where('status', 'completed')->count();
        $abandonedResponses = (clone $responseBase)->where('status', 'abandoned')->count();
        $pendingResponses = $totalResponses - $completedResponses - $abandonedResponses;

        // Previous period comparison
        $periodLength = $from->diffInDays($to);
        $prevFrom = $from->copy()->subDays($periodLength);
        $prevTo = $to->copy()->subDays($periodLength);
        $prevTotal = (clone $responseBase)->whereBetween('started_at', [$prevFrom, $prevTo])->count();
        $responseGrowth = $prevTotal > 0 ? round((($totalResponses - $prevTotal) / $prevTotal) * 100, 1) : ($totalResponses > 0 ? 100 : 0);

        // Survey performance
        $surveyPerformance = $surveys->map(function ($survey) use ($from, $to) {
            $responses = $survey->responses()
                ->whereBetween('started_at', [$from, $to]);

            $total = (clone $responses)->count();
            $completed = (clone $responses)->where('status', 'completed')->count();

            return [
                'id' => $survey->id,
                'title' => $survey->title,
                'status' => $survey->status,
                'total_responses' => $total,
                'completed_responses' => $completed,
                'completion_rate' => $total > 0 ? round($completed / $total * 100, 1) : 0,
                'created_at' => $survey->created_at,
            ];
        })->sortByDesc('total_responses')->values();

        // Sentiment distribution
        $sentimentCounts = (clone $responseBase)
            ->where('status', 'completed')
            ->whereNotNull('sentiment')
            ->selectRaw('sentiment, count(*) as count')
            ->groupBy('sentiment')
            ->pluck('count', 'sentiment');

        // Trend data
        $trend = $this->trend($surveyIds, $from, $to);
        $weeklyTrend = $this->weeklyTrend($surveyIds, $from, $to);

        // Source breakdown
        $sources = (clone $responseBase)
            ->whereNotNull('source')
            ->selectRaw('source, count(*) as count')
            ->groupBy('source')
            ->pluck('count', 'source');

        // Device breakdown
        $devices = (clone $responseBase)
            ->whereNotNull('device')
            ->selectRaw('device, count(*) as count')
            ->groupBy('device')
            ->pluck('count', 'device');

        // Country breakdown
        $countries = (clone $responseBase)
            ->whereNotNull('country')
            ->selectRaw('country, count(*) as count')
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'country');

        // Metrics (NPS, CSAT, etc.)
        $metrics = $this->computeMetrics($surveys, $from, $to);

        // Team/member stats
        $totalMembers = ClientUser::where('client_id', $client->id)->count();
        $activeMembers = ClientUser::where('client_id', $client->id)
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->count();

        // Contact stats
        $totalContacts = Contact::where('client_id', $client->id)->count();
        $consentedContacts = Contact::where('client_id', $client->id)->where('consent', true)->count();

        // Recent activity
        $recentResponses = (clone $responseBase)
            ->with(['survey', 'contact'])
            ->latest('started_at')
            ->limit(20)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'survey_title' => $r->survey->title,
                'contact_name' => $r->contact?->name ?? 'Anonymous',
                'status' => $r->status,
                'sentiment' => $r->sentiment,
                'started_at' => $r->started_at,
                'completed_at' => $r->completed_at,
                'source' => $r->source,
                'device' => $r->device,
            ]);

        // Completion stats per survey
        $completionStats = $surveys->map(function ($survey) use ($from, $to) {
            $responses = $survey->responses()
                ->whereBetween('started_at', [$from, $to]);
            $total = (clone $responses)->count();
            $completed = (clone $responses)->where('status', 'completed')->count();
            $abandoned = (clone $responses)->where('status', 'abandoned')->count();
            
            return [
                'survey_id' => $survey->id,
                'title' => $survey->title,
                'total' => $total,
                'completed' => $completed,
                'abandoned' => $abandoned,
                'pending' => $total - $completed - $abandoned,
                'completion_rate' => $total > 0 ? round($completed / $total * 100, 1) : 0,
            ];
        });

        // Hourly distribution
        $hourly = $this->hourlyDistribution($surveyIds, $from, $to);

        // Day of week
        $dayOfWeek = $this->dayOfWeekDistribution($surveyIds, $from, $to);

        // Campaign stats
        $totalCampaigns = $client->campaigns()->count();
        $sentCampaigns = $client->campaigns()->where('status', 'sent')->count();

        // Review clicks
        $reviewClicks = $this->reviewClickBreakdown($client, null, $from, $to);

        // Avg completion time
        $avgSeconds = (clone $responseBase)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->get(['started_at', 'completed_at'])
            ->map(fn (Response $r) => $r->completed_at->diffInSeconds($r->started_at, true))
            ->avg();

        // Response quality score (based on completion rate and sentiment)
        $qualityScore = $totalResponses > 0
            ? round(
                (($completedResponses / max($totalResponses, 1)) * 0.5 +
                ((int) ($sentimentCounts['positive'] ?? 0) / max($completedResponses, 1)) * 0.5) * 100,
                1
            )
            : 0;

        return [
            'client' => [
                'id' => $client->id,
                'company_name' => $client->company_name,
                'industry' => $client->industry,
                'email' => $client->email,
                'phone' => $client->phone,
                'website' => $client->website,
                'address' => $client->address,
                'status' => $client->status,
                'logo_path' => $client->logo_path,
                'created_at' => $client->created_at,
                'brand_color' => $client->brand_color,
            ],
            'summary' => [
                'total_surveys' => $surveys->count(),
                'active_surveys' => $surveys->where('status', 'published')->count(),
                'total_responses' => $totalResponses,
                'completed_responses' => $completedResponses,
                'abandoned_responses' => $abandonedResponses,
                'pending_responses' => $pendingResponses,
                'completion_rate' => $totalResponses > 0 ? round($completedResponses / max($totalResponses, 1) * 100, 1) : 0,
                'avg_completion_seconds' => $avgSeconds ? (int) round($avgSeconds) : null,
                'response_growth' => $responseGrowth,
                'quality_score' => $qualityScore,
                'total_members' => $totalMembers,
                'active_members' => $activeMembers,
                'total_contacts' => $totalContacts,
                'consented_contacts' => $consentedContacts,
                'total_campaigns' => $totalCampaigns,
                'sent_campaigns' => $sentCampaigns,
                'avg_daily_responses' => $periodLength > 0 ? round($totalResponses / max($periodLength, 1), 1) : 0,
            ],
            'survey_performance' => $surveyPerformance,
            'completion_stats' => $completionStats,
            'sentiment' => [
                'positive' => (int) ($sentimentCounts['positive'] ?? 0),
                'neutral' => (int) ($sentimentCounts['neutral'] ?? 0),
                'negative' => (int) ($sentimentCounts['negative'] ?? 0),
            ],
            'metrics' => $metrics,
            'trend' => $trend,
            'weekly_trend' => $weeklyTrend,
            'hourly_distribution' => $hourly,
            'day_of_week_distribution' => $dayOfWeek,
            'sources' => $sources,
            'devices' => $devices,
            'countries' => $countries,
            'recent_activities' => $recentResponses,
            'review_clicks' => $reviewClicks,
            'surveys' => $surveys->map(fn ($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'status' => $s->status,
                'created_at' => $s->created_at,
                'published_at' => $s->published_at,
                'question_count' => $s->questions()->count(),
            ]),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function reviewClickBreakdown(Client $client, ?Survey $survey, Carbon $from, Carbon $to): array
    {
        $counts = ReviewClick::query()
            ->where('client_id', $client->id)
            ->whereBetween('clicked_at', [$from, $to])
            ->when($survey, fn ($query) => $query->whereHas('response', fn ($r) => $r->where('survey_id', $survey->id)))
            ->selectRaw('channel, count(*) as count')
            ->groupBy('channel')
            ->pluck('count', 'channel');

        return [
            'google_review' => (int) ($counts['google_review'] ?? 0),
            'facebook' => (int) ($counts['facebook'] ?? 0),
            'website' => (int) ($counts['website'] ?? 0),
            'complaint_form' => (int) ($counts['complaint_form'] ?? 0),
            'support_call' => (int) ($counts['support_call'] ?? 0),
            'whatsapp' => (int) ($counts['whatsapp'] ?? 0),
        ];
    }

    /**
     * @param  Collection<int, Survey>  $surveys
     * @return array<string, array<string, mixed>>
     */
    private function computeMetrics(Collection $surveys, Carbon $from, Carbon $to): array
    {
        $buckets = ['nps' => [], 'csat' => [], 'ces' => [], 'rating' => []];

        foreach ($surveys as $survey) {
            if (! $survey->primary_score_question_id) {
                continue;
            }

            $question = $survey->primaryScoreQuestion()->with('questionType')->first();

            if (! $question) {
                continue;
            }

            $metricKey = self::METRIC_KEYS[$question->questionType->key] ?? null;

            if (! $metricKey) {
                continue;
            }

            $max = $this->scaleMaxFor($question->questionType->key, $question->settings ?? []);

            $scores = ResponseAnswer::query()
                ->where('question_id', $question->id)
                ->whereNotNull('score')
                ->whereHas('response', fn ($q) => $q->where('survey_id', $survey->id)
                    ->where('status', 'completed')
                    ->whereBetween('started_at', [$from, $to]))
                ->pluck('score');

            foreach ($scores as $score) {
                $buckets[$metricKey][] = ['score' => (float) $score, 'max' => (float) $max];
            }
        }

        $metrics = [];

        if ($buckets['nps']) {
            $scores = collect($buckets['nps'])->pluck('score');
            $total = $scores->count();
            $promoters = $scores->filter(fn ($s) => $s >= 9)->count();
            $detractors = $scores->filter(fn ($s) => $s <= 6)->count();
            $passives = $total - $promoters - $detractors;

            $metrics['nps'] = [
                'value' => round((($promoters - $detractors) / $total) * 100, 1),
                'promoters' => $promoters,
                'passives' => $passives,
                'detractors' => $detractors,
                'total' => $total,
                'promoter_pct' => $total > 0 ? round($promoters / $total * 100, 1) : 0,
                'detractor_pct' => $total > 0 ? round($detractors / $total * 100, 1) : 0,
                'passive_pct' => $total > 0 ? round($passives / $total * 100, 1) : 0,
            ];
        }

        foreach (['csat', 'ces', 'rating'] as $key) {
            if (! $buckets[$key]) {
                continue;
            }

            $entries = collect($buckets[$key]);
            $avgPct = $entries->avg(fn ($e) => $e['score'] / $e['max'] * 100);

            $metrics[$key] = [
                'value' => round($avgPct, 1),
                'avg_raw' => round($entries->avg('score'), 2),
                'max' => (int) $entries->first()['max'],
                'total' => $entries->count(),
                'percentage' => round($avgPct, 1),
            ];
        }

        return $metrics;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function questionBreakdown(Survey $survey, Carbon $from, Carbon $to): array
    {
        $breakdown = [];

        foreach ($survey->questions as $question) {
            $answers = ResponseAnswer::query()
                ->where('question_id', $question->id)
                ->whereHas('response', fn ($q) => $q->where('status', 'completed')->whereBetween('started_at', [$from, $to]))
                ->get(['answer', 'score']);

            if ($answers->isEmpty()) {
                continue;
            }

            $group = $question->questionType->contract()->builderGroup();

            if ($group === 'choice') {
                $counts = $answers
                    ->flatMap(fn ($a) => is_array($a->answer) ? $a->answer : [$a->answer])
                    ->filter()
                    ->countBy();

                $breakdown[] = ['question' => $question, 'type' => 'choice', 'data' => $counts, 'total' => $answers->count()];
            } elseif ($group === 'scale') {
                $breakdown[] = ['question' => $question, 'type' => 'scale', 'avg' => round($answers->avg('score'), 2), 'total' => $answers->count()];
            } else {
                $breakdown[] = ['question' => $question, 'type' => 'text', 'samples' => $answers->pluck('answer')->filter()->take(5)->values(), 'total' => $answers->count()];
            }
        }

        return $breakdown;
    }

    /**
     * @param  Collection<int, int>  $surveyIds
     * @return array{labels: array<int, string>, series: array<int, int>}
     */
    private function trend(Collection $surveyIds, Carbon $from, Carbon $to): array
    {
        $rows = Response::query()
            ->whereIn('survey_id', $surveyIds)
            ->whereBetween('started_at', [$from, $to])
            ->selectRaw('DATE(started_at) as day, count(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day');

        $labels = [];
        $series = [];
        $cumulative = 0;
        $cursor = $from->copy();

        while ($cursor->lte($to)) {
            $day = $cursor->toDateString();
            $labels[] = $cursor->format('M j');
            $count = (int) ($rows[$day] ?? 0);
            $cumulative += $count;
            $series[] = $count;
            $cursor = $cursor->addDay();
        }

        return ['labels' => $labels, 'series' => $series, 'cumulative' => $cumulative];
    }

    /**
     * @param  Collection<int, int>  $surveyIds
     * @return array{labels: array<int, string>, series: array<int, int>}
     */
    private function weeklyTrend(Collection $surveyIds, Carbon $from, Carbon $to): array
    {
        $rows = Response::query()
            ->whereIn('survey_id', $surveyIds)
            ->whereBetween('started_at', [$from, $to])
            ->selectRaw('YEARWEEK(started_at, 1) as week, count(*) as count')
            ->groupBy('week')
            ->orderBy('week')
            ->pluck('count', 'week');

        $labels = [];
        $series = [];
        $cursor = $from->copy()->startOfWeek();

        while ($cursor->lte($to)) {
            $weekKey = (int) $cursor->format('oW');
            $labels[] = $cursor->format('M j');
            $series[] = (int) ($rows[$weekKey] ?? 0);
            $cursor = $cursor->addWeek();
        }

        return ['labels' => $labels, 'series' => $series];
    }

    /**
     * @param  Collection<int, int>  $surveyIds
     * @return array{labels: array<int, string>, series: array<int, int>}
     */
    private function hourlyDistribution(Collection $surveyIds, Carbon $from, Carbon $to): array
    {
        $rows = Response::query()
            ->whereIn('survey_id', $surveyIds)
            ->whereBetween('started_at', [$from, $to])
            ->selectRaw('HOUR(started_at) as hour, count(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour');

        $labels = [];
        $series = [];
        for ($h = 0; $h < 24; $h++) {
            $labels[] = sprintf('%02d:00', $h);
            $series[] = (int) ($rows[$h] ?? 0);
        }

        return ['labels' => $labels, 'series' => $series];
    }

    /**
     * @param  Collection<int, int>  $surveyIds
     * @return array{labels: array<int, string>, series: array<int, int>}
     */
    private function dayOfWeekDistribution(Collection $surveyIds, Carbon $from, Carbon $to): array
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        
        $rows = Response::query()
            ->whereIn('survey_id', $surveyIds)
            ->whereBetween('started_at', [$from, $to])
            ->selectRaw('DAYOFWEEK(started_at) as dow, count(*) as count')
            ->groupBy('dow')
            ->pluck('count', 'dow');

        $labels = [];
        $series = [];
        for ($i = 1; $i <= 7; $i++) {
            $labels[] = $days[$i - 1];
            $series[] = (int) ($rows[$i] ?? 0);
        }

        return ['labels' => $labels, 'series' => $series];
    }

    private function dropOffBreakdown(Survey $survey, Carbon $from, Carbon $to): array
    {
        $dropOff = Response::query()
            ->where('survey_id', $survey->id)
            ->where('status', '!=', 'completed')
            ->whereNotNull('last_question_id')
            ->whereBetween('started_at', [$from, $to])
            ->selectRaw('last_question_id, count(*) as drop_count')
            ->groupBy('last_question_id')
            ->pluck('drop_count', 'last_question_id');

        $result = [];
        foreach ($survey->questions as $question) {
            $count = (int) ($dropOff[$question->id] ?? 0);
            if ($count > 0) {
                $result[] = [
                    'question_text' => Str::limit($question->question_text, 60),
                    'drop_count' => $count,
                ];
            }
        }

        return $result;
    }

    private function scaleMaxFor(string $typeKey, array $settings): int
    {
        return (int) ($settings['scale_max'] ?? match ($typeKey) {
            'nps' => 10,
            'csat' => 5,
            'ces' => 7,
            'rating_stars' => $settings['max_stars'] ?? 5,
            'emoji_rating' => 5,
            default => 5,
        });
    }
}