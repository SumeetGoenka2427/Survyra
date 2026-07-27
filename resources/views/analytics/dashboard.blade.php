@php
    $metricLabels = ['nps' => 'NPS', 'csat' => 'CSAT', 'ces' => 'CES', 'rating' => 'Avg. Rating'];
    $primaryMetricKey = collect(['nps', 'csat', 'ces', 'rating'])->first(fn ($key) => isset($snapshot['metrics'][$key]));
    $insightColors = ['positive' => 'success', 'warning' => 'warning', 'info' => 'info'];

    // Flattened, JS-friendly shape for the question-breakdown charts (choice -> bar, scale -> radial ring)
    $questionBreakdownJs = collect($snapshot['question_breakdown'])->map(function ($item) {
        if ($item['type'] === 'choice') {
            return ['type' => 'choice', 'labels' => $item['data']->keys()->values(), 'values' => $item['data']->values()->values()];
        }
        if ($item['type'] === 'scale') {
            return ['type' => 'scale', 'avg' => $item['avg'], 'max' => 10];
        }
        return ['type' => 'text'];
    })->values();

    $fragmentChartData = [
        'completion_rate' => $snapshot['completion_rate'],
        'abandonment_rate' => $snapshot['abandonment_rate'],
        'devices' => $snapshot['devices'],
        'browsers' => $snapshot['browsers'],
        'sources' => $snapshot['sources'],
        'countries' => $snapshot['countries'],
        'hour_day_heatmap' => $snapshot['hour_day_heatmap'],
        'drop_off' => $snapshot['drop_off'],
        'review_clicks' => $snapshot['review_clicks'],
        'question_breakdown_js' => $questionBreakdownJs,
    ];
@endphp

<!-- KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="ds-kpi-card">
            <div class="d-flex align-items-center gap-3">
                <div class="ds-kpi-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-inboxes"></i></div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Total Responses</div>
                    <div class="fw-bold fs-4">{{ number_format($snapshot['total_responses']) }}</div>
                    @if ($snapshot['growth_rate'] != 0)
                        <span class="ds-kpi-trend {{ $snapshot['growth_rate'] > 0 ? 'up' : 'down' }}">
                            <i class="bi bi-{{ $snapshot['growth_rate'] > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                            {{ abs($snapshot['growth_rate']) }}%
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="ds-kpi-card">
            <div class="d-flex align-items-center gap-3">
                <div class="ds-kpi-icon bg-success bg-opacity-10 text-success"><i class="bi bi-check2-circle"></i></div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Completion Rate</div>
                    <div class="fw-bold fs-4">{{ $snapshot['completion_rate'] }}%</div>
                    <span class="text-muted small">{{ number_format($snapshot['completed_count']) }} / {{ number_format($snapshot['total_responses']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="ds-kpi-card">
            <div class="d-flex align-items-center gap-3">
                <div class="ds-kpi-icon bg-info bg-opacity-10 text-info"><i class="bi bi-ui-checks"></i></div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Active Surveys</div>
                    <div class="fw-bold fs-4">{{ $snapshot['active_surveys'] }}</div>
                    <span class="text-muted small">{{ $snapshot['total_surveys'] }} total</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="ds-kpi-card">
            <div class="d-flex align-items-center gap-3">
                <div class="ds-kpi-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-stopwatch"></i></div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Avg. Completion Time</div>
                    <div class="fw-bold fs-4">{{ $snapshot['avg_completion_seconds'] ? gmdate('i:s', $snapshot['avg_completion_seconds']) : '—' }}</div>
                    <span class="text-muted small">minutes:seconds</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="ds-kpi-card">
            <div class="d-flex align-items-center gap-3">
                <div class="ds-kpi-icon text-purple" style="background: rgba(139,92,246,.1); color:#8b5cf6;"><i class="bi bi-stars"></i></div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.05em;">{{ $primaryMetricKey ? $metricLabels[$primaryMetricKey] : 'Primary Metric' }}</div>
                    <div class="fw-bold fs-4">
                        {{ $primaryMetricKey ? ($primaryMetricKey === 'nps' ? $snapshot['metrics'][$primaryMetricKey]['value'] : $snapshot['metrics'][$primaryMetricKey]['value'].'%') : '—' }}
                    </div>
                    <span class="text-muted small">{{ $primaryMetricKey ? $snapshot['metrics'][$primaryMetricKey]['total'].' scored' : 'no scoring question' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="ds-kpi-card">
            <div class="d-flex align-items-center gap-3">
                <div class="ds-kpi-icon" style="background: rgba(244,63,94,.1); color:#f43f5e;"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Avg. Daily</div>
                    <div class="fw-bold fs-4">{{ $snapshot['avg_daily_responses'] }}</div>
                    <span class="text-muted small">responses/day</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Insights & Recommendations -->
@if (count($snapshot['insights']) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent"><strong><i class="bi bi-lightbulb me-2 text-primary"></i>Insights &amp; Recommendations</strong></div>
        <div class="card-body">
            <div class="row g-3">
                @foreach ($snapshot['insights'] as $insight)
                    <div class="col-md-6">
                        <div class="d-flex gap-3 p-3 rounded-3 bg-{{ $insightColors[$insight['type']] ?? 'secondary' }} bg-opacity-10">
                            <div class="text-{{ $insightColors[$insight['type']] ?? 'secondary' }} fs-4"><i class="bi {{ $insight['icon'] }}"></i></div>
                            <div>
                                <div class="fw-semibold">{{ $insight['title'] }}</div>
                                <div class="small text-muted">{{ $insight['description'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

@if (isset($survey) && $survey)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent d-flex align-items-center gap-2">
            <strong><i class="bi bi-robot me-1 text-primary"></i>AI Insights</strong>
            <span class="badge bg-light text-dark">{{ $survey->title }}</span>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 mb-3" id="ai-insights-buttons" data-survey-id="{{ $survey->id }}">
                <button type="button" class="btn btn-sm btn-outline-primary" data-ai-action="quality-score" data-ai-url="{{ route('admin.surveys.ai.quality-score', $survey) }}">
                    <i class="bi bi-award"></i> Quality Score
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" data-ai-action="summary" data-ai-url="{{ route('admin.surveys.ai.summary', $survey) }}">
                    <i class="bi bi-file-text"></i> Response Summary
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" data-ai-action="sentiment" data-ai-url="{{ route('admin.surveys.ai.sentiment', $survey) }}">
                    <i class="bi bi-emoji-smile"></i> Sentiment Analysis
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" data-ai-action="keywords" data-ai-url="{{ route('admin.surveys.ai.keywords', $survey) }}">
                    <i class="bi bi-tags"></i> Keywords
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" data-ai-action="actions" data-ai-url="{{ route('admin.surveys.ai.actions', $survey) }}">
                    <i class="bi bi-list-check"></i> Recommended Actions
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" data-ai-action="executive-report" data-ai-url="{{ route('admin.surveys.ai.executive-report', $survey) }}">
                    <i class="bi bi-file-earmark-richtext"></i> Executive Report
                </button>
            </div>
            <div id="ai-insights-results"></div>
        </div>
    </div>
@endif

<!-- Survey Performance Comparison -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <strong><i class="bi bi-table me-2 text-primary"></i>Survey Performance</strong>
        <input type="search" id="sd-survey-search" class="form-control form-control-sm" style="max-width: 220px;" placeholder="Search surveys…">
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="sd-survey-performance-table">
            <thead>
                <tr>
                    <th>Survey</th>
                    <th>Status</th>
                    <th role="button" data-sort-key="total"><i class="bi bi-arrow-down-up small"></i> Responses</th>
                    <th role="button" data-sort-key="rate"><i class="bi bi-arrow-down-up small"></i> Completion</th>
                    <th role="button" data-sort-key="metric"><i class="bi bi-arrow-down-up small"></i> Metric</th>
                    <th>Avg. Time</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($snapshot['survey_performance'] as $row)
                    <tr data-row data-title="{{ Str::lower($row['title']) }}" data-total="{{ $row['total_responses'] }}" data-rate="{{ $row['completion_rate'] }}" data-metric="{{ $row['metric_value'] ?? 0 }}">
                        <td>
                            <div class="fw-medium">{{ $row['title'] }}</div>
                            <div class="small text-muted">Created {{ $row['created_at']?->diffForHumans() }}</div>
                        </td>
                        <td>
                            @php $statusColors = ['published' => 'success', 'draft' => 'secondary', 'archived' => 'warning']; @endphp
                            <span class="badge bg-{{ $statusColors[$row['status']] ?? 'secondary' }}">{{ ucfirst($row['status']) }}</span>
                        </td>
                        <td class="fw-semibold">{{ number_format($row['total_responses']) }}</td>
                        <td>
                            <span class="fw-semibold">{{ $row['completion_rate'] }}%</span>
                            <div class="progress mt-1" style="height: 5px; width: 90px;">
                                <div class="progress-bar bg-success" style="width: {{ $row['completion_rate'] }}%"></div>
                            </div>
                        </td>
                        <td>
                            @if ($row['metric_key'])
                                <span class="badge bg-light text-dark">{{ $metricLabels[$row['metric_key']] ?? strtoupper($row['metric_key']) }}: {{ $row['metric_value'] }}{{ $row['metric_key'] !== 'nps' ? '%' : '' }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $row['avg_completion_seconds'] ? gmdate('i:s', $row['avg_completion_seconds']) : '—' }}</td>
                        <td>
                            <a href="{{ route('admin.analytics.index', ['client_id' => request('client_id'), 'survey_id' => $row['id']]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No survey data in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Heatmap + Progress Rings -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent"><strong><i class="bi bi-grid-3x3 me-2 text-primary"></i>Response Density (Hour &times; Day)</strong></div>
            <div class="card-body"><div id="sd-heatmap-chart"></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="row g-3 h-100">
            <div class="col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent"><strong class="small">Completion</strong></div>
                    <div class="card-body d-flex align-items-center justify-content-center"><div id="sd-completion-ring" style="width:100%;"></div></div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent"><strong class="small">Abandonment</strong></div>
                    <div class="card-body d-flex align-items-center justify-content-center"><div id="sd-abandonment-ring" style="width:100%;"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Devices / Browsers / Sources / Countries -->
@if (count($snapshot['devices']) > 0 || count($snapshot['browsers']) > 0 || count($snapshot['sources']) > 0 || count($snapshot['countries'] ?? []) > 0)
    <div class="row g-3 mb-4">
        @if (count($snapshot['devices']) > 0)
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent"><strong><i class="bi bi-phone me-2 text-primary"></i>Devices</strong></div>
                    <div class="card-body"><div id="sd-devices-chart"></div></div>
                </div>
            </div>
        @endif
        @if (count($snapshot['browsers']) > 0)
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent"><strong><i class="bi bi-window me-2 text-primary"></i>Browsers</strong></div>
                    <div class="card-body"><div id="sd-browsers-chart"></div></div>
                </div>
            </div>
        @endif
        @if (count($snapshot['sources']) > 0)
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent"><strong><i class="bi bi-diagram-3 me-2 text-primary"></i>Sources</strong></div>
                    <div class="card-body"><div id="sd-sources-chart"></div></div>
                </div>
            </div>
        @endif
        @if (count($snapshot['countries'] ?? []) > 0)
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent"><strong><i class="bi bi-geo-alt me-2 text-primary"></i>Top Countries</strong></div>
                    <div class="card-body"><div id="sd-countries-chart"></div></div>
                </div>
            </div>
        @endif
    </div>
@endif

<!-- Drop-off Funnel -->
@if (count($snapshot['drop_off']) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-funnel me-2 text-primary"></i>Drop-off Funnel</strong>
            <span class="badge bg-light text-dark">{{ array_sum(array_column($snapshot['drop_off'], 'drop_count')) }} total drop-offs</span>
        </div>
        <div class="card-body"><div id="sd-dropoff-chart"></div></div>
    </div>
@endif

<!-- Question Breakdown (only when a specific survey is selected) -->
@if (count($snapshot['question_breakdown']) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent"><strong><i class="bi bi-list-check me-2 text-primary"></i>Question Breakdown</strong></div>
        <div class="card-body">
            @foreach ($snapshot['question_breakdown'] as $index => $item)
                <div class="mb-4">
                    <div class="fw-semibold mb-2">{{ $item['question']->question_text }}</div>
                    @if ($item['type'] === 'choice' || $item['type'] === 'scale')
                        <div id="sd-qb-{{ $index }}"></div>
                    @else
                        <ul class="small text-muted mb-0">
                            @foreach ($item['samples'] as $sample)
                                <li>{{ is_array($sample) ? implode(', ', $sample) : $sample }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- Review & Action Clicks -->
@php $totalReviewClicks = array_sum($snapshot['review_clicks']); @endphp
@if ($totalReviewClicks > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent"><strong><i class="bi bi-hand-index-thumb me-2 text-primary"></i>Review &amp; Action Clicks</strong></div>
        <div class="card-body"><div id="sd-review-clicks-chart"></div></div>
    </div>
@endif

<!-- Recent Activity Timeline -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent"><strong><i class="bi bi-clock-history me-2 text-primary"></i>Recent Activity</strong></div>
    <div class="card-body">
        @forelse ($snapshot['recent_responses'] as $response)
            <div class="d-flex gap-3 pb-3 mb-3 border-bottom">
                <div class="flex-shrink-0">
                    @php
                        $dotColor = $response->status === 'completed' ? 'success' : ($response->status === 'abandoned' ? 'secondary' : 'warning');
                        $sentimentIcon = ['positive' => 'emoji-smile text-success', 'negative' => 'emoji-frown text-danger', 'neutral' => 'emoji-neutral text-secondary'][$response->sentiment] ?? 'question-circle text-muted';
                    @endphp
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $dotColor }} bg-opacity-10 text-{{ $dotColor }}" style="width:36px;height:36px;">
                        <i class="bi bi-{{ $sentimentIcon }}"></i>
                    </span>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <span class="fw-medium">{{ $response->survey->title }}</span>
                            <span class="badge bg-{{ $dotColor }} ms-2">{{ ucfirst($response->status) }}</span>
                            @if ($response->sentiment)
                                <span class="badge bg-{{ $response->sentiment === 'positive' ? 'success' : ($response->sentiment === 'negative' ? 'danger' : 'secondary') }}">{{ ucfirst($response->sentiment) }}</span>
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-view-response="{{ $response->id }}">View</button>
                    </div>
                    <div class="small text-muted mt-1">Started {{ $response->started_at?->diffForHumans() }}</div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4">No responses in this range.</div>
        @endforelse
    </div>
</div>

<script type="application/json" id="analytics-fragment-chart-data">
    @json($fragmentChartData)
</script>
