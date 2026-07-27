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

        // Completion rate broken down by device — feeds both a chart and the insights engine
        $deviceCompletionRates = $this->deviceCompletionRates($surveyIds, $from, $to);

        // Per-survey comparison (completion rate, volume, primary metric)
        $surveyPerformance = $this->surveyPerformance($surveys, $from, $to);

        // Joint hour x day-of-week distribution, for the heatmap (hourly_distribution/
        // day_of_week_distribution above are marginals only, not a true cross-tab)
        $hourDayHeatmap = $this->hourByDayHeatmap($surveyIds, $from, $to);

        $snapshot = [
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
            'device_completion_rates' => $deviceCompletionRates,
            'survey_performance' => $surveyPerformance,
            'hour_day_heatmap' => $hourDayHeatmap,
        ];

        $snapshot['insights'] = $this->generateInsights($snapshot);

        return $snapshot;
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

        // Survey performance (shared computation — see surveyPerformance())
        $surveyPerformance = $this->surveyPerformance($surveys, $from, $to);

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
            ->where('is_active', true)
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
     * Raw `started_at` timestamps for the period — shared by every bucketing
     * method below so week/hour/day-of-week grouping can happen in PHP via
     * Carbon instead of driver-specific SQL functions (MySQL's YEARWEEK/HOUR/
     * DAYOFWEEK have no SQLite equivalent, which broke the whole analytics
     * suite under the test DB).
     *
     * @param  Collection<int, int>  $surveyIds
     * @return Collection<int, Carbon>
     */
    private function responseTimestamps(Collection $surveyIds, Carbon $from, Carbon $to): Collection
    {
        return Response::query()
            ->whereIn('survey_id', $surveyIds)
            ->whereBetween('started_at', [$from, $to])
            ->pluck('started_at');
    }

    /**
     * @param  Collection<int, int>  $surveyIds
     * @return array{labels: array<int, string>, series: array<int, int>}
     */
    private function weeklyTrend(Collection $surveyIds, Carbon $from, Carbon $to): array
    {
        $counts = $this->responseTimestamps($surveyIds, $from, $to)
            ->countBy(fn ($timestamp) => Carbon::parse($timestamp)->format('oW'));

        $labels = [];
        $series = [];
        $cursor = $from->copy()->startOfWeek();

        while ($cursor->lte($to)) {
            $weekKey = $cursor->format('oW');
            $labels[] = $cursor->format('M j');
            $series[] = (int) ($counts[$weekKey] ?? 0);
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
        $counts = $this->responseTimestamps($surveyIds, $from, $to)
            ->countBy(fn ($timestamp) => Carbon::parse($timestamp)->hour);

        $labels = [];
        $series = [];
        for ($h = 0; $h < 24; $h++) {
            $labels[] = sprintf('%02d:00', $h);
            $series[] = (int) ($counts[$h] ?? 0);
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

        // Carbon::dayOfWeek is 0 (Sunday) .. 6 (Saturday); keep the same
        // 1-indexed (Sunday..Saturday) shape the view already expects.
        $counts = $this->responseTimestamps($surveyIds, $from, $to)
            ->countBy(fn ($timestamp) => Carbon::parse($timestamp)->dayOfWeek + 1);

        $labels = [];
        $series = [];
        for ($i = 1; $i <= 7; $i++) {
            $labels[] = $days[$i - 1];
            $series[] = (int) ($counts[$i] ?? 0);
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

    /**
     * Completion rate per device — used for a chart and to power the
     * "mobile completes worse than desktop"-style insight below.
     *
     * @param  Collection<int, int>  $surveyIds
     * @return array<string, float>
     */
    private function deviceCompletionRates(Collection $surveyIds, Carbon $from, Carbon $to): array
    {
        $rows = Response::query()
            ->whereIn('survey_id', $surveyIds)
            ->whereBetween('started_at', [$from, $to])
            ->whereNotNull('device')
            ->selectRaw('device, status, count(*) as count')
            ->groupBy('device', 'status')
            ->get()
            ->groupBy('device');

        return $rows->map(function (Collection $statuses) {
            $total = $statuses->sum('count');
            $completed = $statuses->firstWhere('status', 'completed')->count ?? 0;

            return $total > 0 ? round($completed / $total * 100, 1) : 0.0;
        })->toArray();
    }

    /**
     * One row per survey: volume, completion rate, avg completion time, and
     * primary score metric if the survey has one. Shared by both the survey
     * analytics dashboard (comparison table across a client's surveys) and
     * the client analytics dashboard (its existing survey-performance table).
     *
     * @param  Collection<int, Survey>  $surveys
     * @return Collection<int, array<string, mixed>>
     */
    private function surveyPerformance(Collection $surveys, Carbon $from, Carbon $to): Collection
    {
        return $surveys->map(function (Survey $survey) use ($from, $to) {
            $responses = $survey->responses()->whereBetween('started_at', [$from, $to]);
            $total = (clone $responses)->count();
            $completed = (clone $responses)->where('status', 'completed')->count();

            $avgSeconds = (clone $responses)
                ->where('status', 'completed')
                ->whereNotNull('completed_at')
                ->get(['started_at', 'completed_at'])
                ->map(fn (Response $r) => $r->completed_at->diffInSeconds($r->started_at, true))
                ->avg();

            $metricKey = null;
            $metricValue = null;

            if ($survey->primary_score_question_id) {
                $question = $survey->primaryScoreQuestion()->with('questionType')->first();
                $key = $question ? (self::METRIC_KEYS[$question->questionType->key] ?? null) : null;

                if ($key) {
                    $computed = $this->computeMetrics(collect([$survey]), $from, $to);

                    if (isset($computed[$key])) {
                        $metricKey = $key;
                        $metricValue = $computed[$key]['value'];
                    }
                }
            }

            return [
                'id' => $survey->id,
                'title' => $survey->title,
                'status' => $survey->status,
                'total_responses' => $total,
                'completed_responses' => $completed,
                'completion_rate' => $total > 0 ? round($completed / $total * 100, 1) : 0,
                'avg_completion_seconds' => $avgSeconds ? (int) round($avgSeconds) : null,
                'metric_key' => $metricKey,
                'metric_value' => $metricValue,
                'created_at' => $survey->created_at,
            ];
        })->sortByDesc('total_responses')->values();
    }

    /**
     * Rule-based insight cards derived purely from an already-computed
     * snapshot — no AI call, no new data source, just thresholds over
     * numbers the dashboard already has.
     *
     * @return array<int, array{type: string, icon: string, title: string, description: string}>
     */
    private function generateInsights(array $snapshot): array
    {
        $insights = [];

        if ($snapshot['total_responses'] >= 5) {
            if ($snapshot['growth_rate'] >= 10) {
                $insights[] = [
                    'type' => 'positive',
                    'icon' => 'bi-graph-up-arrow',
                    'title' => 'Response volume is growing',
                    'description' => "Responses are up {$snapshot['growth_rate']}% versus the previous period.",
                ];
            } elseif ($snapshot['growth_rate'] <= -10) {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'bi-graph-down-arrow',
                    'title' => 'Response volume is declining',
                    'description' => 'Responses are down '.abs($snapshot['growth_rate']).'% versus the previous period — check distribution channels or campaign activity.',
                ];
            }
        }

        if ($snapshot['total_responses'] > 0) {
            if ($snapshot['completion_rate'] < 50) {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'bi-exclamation-triangle',
                    'title' => 'Low completion rate',
                    'description' => "Only {$snapshot['completion_rate']}% of respondents complete the survey — {$snapshot['abandonment_rate']}% abandon before finishing. Consider shortening the survey or reviewing logic/skip rules.",
                ];
            } elseif ($snapshot['completion_rate'] >= 85) {
                $insights[] = [
                    'type' => 'positive',
                    'icon' => 'bi-check2-circle',
                    'title' => 'Strong completion rate',
                    'description' => "{$snapshot['completion_rate']}% of respondents who start the survey finish it.",
                ];
            }
        }

        $sentimentTotal = array_sum($snapshot['sentiment_counts']);
        if ($sentimentTotal >= 5) {
            $negativePct = round($snapshot['sentiment_counts']['negative'] / $sentimentTotal * 100, 1);
            $positivePct = round($snapshot['sentiment_counts']['positive'] / $sentimentTotal * 100, 1);

            if ($negativePct >= 30) {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'bi-emoji-frown',
                    'title' => 'Elevated negative sentiment',
                    'description' => "{$negativePct}% of sentiment-tagged responses are negative — review recent negative feedback for recurring issues.",
                ];
            } elseif ($positivePct >= 70) {
                $insights[] = [
                    'type' => 'positive',
                    'icon' => 'bi-emoji-smile',
                    'title' => 'Positive sentiment is strong',
                    'description' => "{$positivePct}% of sentiment-tagged responses are positive.",
                ];
            }
        }

        if (isset($snapshot['metrics']['nps'])) {
            $nps = $snapshot['metrics']['nps']['value'];

            if ($nps >= 50) {
                $insights[] = [
                    'type' => 'positive',
                    'icon' => 'bi-trophy',
                    'title' => 'Excellent NPS',
                    'description' => "An NPS of {$nps} means promoters strongly outweigh detractors.",
                ];
            } elseif ($nps < 0) {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'bi-emoji-dizzy',
                    'title' => 'Negative NPS',
                    'description' => "An NPS of {$nps} means detractors currently outweigh promoters.",
                ];
            }
        }

        $deviceRates = $snapshot['device_completion_rates'];
        if (isset($deviceRates['mobile'], $deviceRates['desktop']) && $deviceRates['desktop'] > 0) {
            $gap = round($deviceRates['desktop'] - $deviceRates['mobile'], 1);

            if ($gap >= 15) {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'bi-phone',
                    'title' => 'Mobile respondents complete less often',
                    'description' => "Mobile completion is {$deviceRates['mobile']}% vs {$deviceRates['desktop']}% on desktop — consider a shorter or more mobile-friendly layout.",
                ];
            }
        }

        $strugglingSurvey = collect($snapshot['survey_performance'] ?? [])
            ->first(fn ($s) => $s['total_responses'] >= 10 && $s['completion_rate'] < 40);

        if ($strugglingSurvey) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-flag',
                'title' => 'One survey is under-performing',
                'description' => "\"{$strugglingSurvey['title']}\" has a {$strugglingSurvey['completion_rate']}% completion rate across {$strugglingSurvey['total_responses']} responses — worth reviewing its length or question order.",
            ];
        }

        if ($snapshot['avg_completion_seconds'] && $snapshot['avg_completion_seconds'] > 300) {
            $minutes = round($snapshot['avg_completion_seconds'] / 60, 1);
            $insights[] = [
                'type' => 'info',
                'icon' => 'bi-stopwatch',
                'title' => 'Long average completion time',
                'description' => "Respondents take {$minutes} minutes on average — surveys over 5 minutes tend to see higher abandonment.",
            ];
        }

        return $insights;
    }

    /**
     * True joint hour x day-of-week distribution (not the two marginals
     * above) — one series per day, shaped for an ApexCharts heatmap.
     *
     * @param  Collection<int, int>  $surveyIds
     * @return array<int, array{name: string, data: array<int, array{x: string, y: int}>}>
     */
    private function hourByDayHeatmap(Collection $surveyIds, Carbon $from, Carbon $to): array
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $grid = array_fill(0, 7, array_fill(0, 24, 0));

        foreach ($this->responseTimestamps($surveyIds, $from, $to) as $timestamp) {
            $t = Carbon::parse($timestamp);
            $grid[$t->dayOfWeek][$t->hour]++;
        }

        $series = [];
        foreach ($days as $index => $day) {
            $data = [];
            for ($h = 0; $h < 24; $h++) {
                $data[] = ['x' => sprintf('%02d:00', $h), 'y' => $grid[$index][$h]];
            }
            $series[] = ['name' => $day, 'data' => $data];
        }

        return $series;
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