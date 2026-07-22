@php
    $metricLabels = ['nps' => 'NPS', 'csat' => 'CSAT', 'ces' => 'CES', 'rating' => 'Avg. Rating'];
@endphp

<div class="row g-3 mb-4">
    <div class="col-md-3"><x-stat-card label="Total Responses" :value="$snapshot['total_responses']" icon="bi-inboxes" color="primary" /></div>
    <div class="col-md-3"><x-stat-card label="Today" :value="$snapshot['today_responses']" icon="bi-calendar-day" color="info" /></div>
    <div class="col-md-3"><x-stat-card label="Completion Rate" :value="$snapshot['completion_rate'].'%'" icon="bi-check2-circle" color="success" /></div>
    <div class="col-md-3">
        <x-stat-card
            label="Avg. Completion Time"
            :value="$snapshot['avg_completion_seconds'] ? gmdate('i:s', $snapshot['avg_completion_seconds']) : '—'"
            icon="bi-stopwatch"
            color="warning"
        />
    </div>
</div>

@if (count($snapshot['metrics']) > 0)
    <div class="row g-3 mb-4">
        @foreach ($snapshot['metrics'] as $key => $metric)
            <div class="col-md-3">
                <x-stat-card
                    :label="$metricLabels[$key] ?? strtoupper($key)"
                    :value="$key === 'nps' ? $metric['value'] : $metric['value'].'%'"
                    icon="bi-graph-up-arrow"
                    color="dark"
                />
            </div>
        @endforeach
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><strong>Positive Feedback</strong></div>
            <ul class="list-group list-group-flush">
                @forelse ($snapshot['positive_responses'] as $response)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $response->survey->title }}</span>
                        <span class="d-flex align-items-center gap-2">
                            <span class="text-muted small">{{ $response->completed_at?->diffForHumans() }}</span>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-view-response="{{ $response->id }}">View</button>
                        </span>
                    </li>
                @empty
                    <li class="list-group-item text-muted small">No positive feedback in this range.</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><strong>Negative Feedback</strong></div>
            <ul class="list-group list-group-flush">
                @forelse ($snapshot['negative_responses'] as $response)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ $response->survey->title }}</span>
                        <span class="d-flex align-items-center gap-2">
                            <span class="text-muted small">{{ $response->completed_at?->diffForHumans() }}</span>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-view-response="{{ $response->id }}">View</button>
                        </span>
                    </li>
                @empty
                    <li class="list-group-item text-muted small">No negative feedback in this range.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

@if (count($snapshot['devices']) > 0 || count($snapshot['browsers']) > 0 || count($snapshot['sources']) > 0 || count($snapshot['countries'] ?? []) > 0)
    <div class="row g-3 mb-4">
        @if (count($snapshot['devices']) > 0)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><strong>Devices</strong></div>
                <div class="card-body">
                    @php $deviceTotal = array_sum($snapshot['devices']->toArray()); @endphp
                    @foreach ($snapshot['devices'] as $device => $count)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="small text-muted" style="width: 80px;">{{ ucfirst($device) }}</div>
                            <div class="progress flex-grow-1" style="height: 8px;">
                                <div class="progress-bar" style="width: {{ $deviceTotal ? round($count / $deviceTotal * 100) : 0 }}%"></div>
                            </div>
                            <div class="small text-muted" style="width: 40px;">{{ $count }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        @if (count($snapshot['browsers']) > 0)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><strong>Browsers</strong></div>
                <div class="card-body">
                    @php $browserTotal = array_sum($snapshot['browsers']->toArray()); @endphp
                    @foreach ($snapshot['browsers'] as $browser => $count)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="small text-muted" style="width: 100px;">{{ $browser }}</div>
                            <div class="progress flex-grow-1" style="height: 8px;">
                                <div class="progress-bar" style="width: {{ $browserTotal ? round($count / $browserTotal * 100) : 0 }}%"></div>
                            </div>
                            <div class="small text-muted" style="width: 40px;">{{ $count }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        @if (count($snapshot['sources']) > 0)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><strong>Sources</strong></div>
                <div class="card-body">
                    @php $sourceTotal = array_sum($snapshot['sources']->toArray()); @endphp
                    @foreach ($snapshot['sources'] as $source => $count)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="small text-muted" style="width: 80px;">{{ ucfirst($source) }}</div>
                            <div class="progress flex-grow-1" style="height: 8px;">
                                <div class="progress-bar" style="width: {{ $sourceTotal ? round($count / $sourceTotal * 100) : 0 }}%"></div>
                            </div>
                            <div class="small text-muted" style="width: 40px;">{{ $count }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        @if (count($snapshot['countries'] ?? []) > 0)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white"><strong>Top Countries</strong></div>
                <div class="card-body">
                    @php $countryTotal = array_sum($snapshot['countries']->toArray()); @endphp
                    @foreach ($snapshot['countries'] as $country => $count)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="small text-muted" style="width: 100px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $country }}</div>
                            <div class="progress flex-grow-1" style="height: 8px;">
                                <div class="progress-bar bg-info" style="width: {{ $countryTotal ? round($count / $countryTotal * 100) : 0 }}%"></div>
                            </div>
                            <div class="small text-muted" style="width: 30px;">{{ $count }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
@endif

@if (count($snapshot['drop_off']) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Drop-off Funnel</strong>
            <span class="badge text-bg-secondary">{{ array_sum(array_column($snapshot['drop_off'], 'drop_count')) }} total drop-offs</span>
        </div>
        <div class="card-body">
            @php
                $maxDrop = max(array_column($snapshot['drop_off'], 'drop_count'));
                $totalStarted = $snapshot['total_responses'] + array_sum(array_column($snapshot['drop_off'], 'drop_count'));
            @endphp
            <div class="drop-off-funnel">
                @foreach ($snapshot['drop_off'] as $index => $item)
                    @php
                        $width = $maxDrop > 0 ? round($item['drop_count'] / $maxDrop * 100) : 0;
                        $retention = $totalStarted > 0 ? round(($totalStarted - $item['drop_count']) / $totalStarted * 100) : 0;
                    @endphp
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="small text-muted" style="width: 30px; text-align: right;">#{{ $index + 1 }}</div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-truncate" style="max-width: 300px;">{{ $item['question_text'] }}</span>
                                <span class="text-danger fw-semibold">-{{ $item['drop_count'] }} ({{ 100 - $retention }}%)</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 24px;">
                                    <div class="progress-bar bg-danger" role="progressbar"
                                         style="width: {{ $width }}%"
                                         aria-valuenow="{{ $item['drop_count'] }}" aria-valuemin="0" aria-valuemax="{{ $maxDrop }}">
                                    </div>
                                </div>
                                <div class="small text-muted" style="width: 50px;">{{ $item['drop_count'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($totalStarted > 0)
                <div class="mt-3 pt-2 border-top d-flex justify-content-between text-muted small">
                    <span>Total started: {{ $totalStarted }}</span>
                    <span>Completed: {{ $snapshot['total_responses'] }} ({{ $totalStarted > 0 ? round($snapshot['total_responses'] / $totalStarted * 100) : 0 }}%)</span>
                    <span>Overall drop-off: {{ $totalStarted > 0 ? round(array_sum(array_column($snapshot['drop_off'], 'drop_count')) / $totalStarted * 100) : 0 }}%</span>
                </div>
            @endif
        </div>
    </div>
@endif

@if (count($snapshot['question_breakdown']) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><strong>Question Breakdown</strong></div>
        <div class="card-body">
            @foreach ($snapshot['question_breakdown'] as $item)
                <div class="mb-3">
                    <div class="fw-semibold mb-2">{{ $item['question']->question_text }}</div>
                    @if ($item['type'] === 'choice')
                        @php $total = max($item['total'], 1); @endphp
                        @foreach ($item['data'] as $option => $count)
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="small text-muted" style="width: 160px;">{{ $option }}</div>
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar" style="width: {{ round($count / $total * 100) }}%"></div>
                                </div>
                                <div class="small text-muted" style="width: 50px;">{{ $count }}</div>
                            </div>
                        @endforeach
                    @elseif ($item['type'] === 'scale')
                        <div class="fs-5">{{ $item['avg'] }} <span class="text-muted small">avg &middot; {{ $item['total'] }} response(s)</span></div>
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

@php
    $reviewClickLabels = [
        'google_review' => ['Google Review', 'bi-google'],
        'facebook' => ['Facebook', 'bi-facebook'],
        'website' => ['Website', 'bi-globe'],
        'whatsapp' => ['WhatsApp', 'bi-whatsapp'],
        'support_call' => ['Support Call', 'bi-telephone'],
        'complaint_form' => ['Complaint Form', 'bi-chat-left-text'],
    ];
    $totalReviewClicks = array_sum($snapshot['review_clicks']);
@endphp
@if ($totalReviewClicks > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><strong>Review &amp; Action Clicks</strong></div>
        <div class="card-body">
            <div class="row g-3 text-center">
                @foreach ($reviewClickLabels as $channel => [$label, $icon])
                    <div class="col-6 col-md-2">
                        <i class="bi {{ $icon }} d-block fs-4 text-muted mb-1"></i>
                        <div class="fs-5 fw-bold">{{ $snapshot['review_clicks'][$channel] }}</div>
                        <div class="text-muted small">{{ $label }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><strong>Recent Responses</strong></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Survey</th>
                    <th>Status</th>
                    <th>Sentiment</th>
                    <th>Started</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($snapshot['recent_responses'] as $response)
                    <tr>
                        <td>{{ $response->survey->title }}</td>
                        <td><span class="badge text-bg-{{ $response->status === 'completed' ? 'success' : ($response->status === 'abandoned' ? 'secondary' : 'warning') }}">{{ ucfirst($response->status) }}</span></td>
                        <td>
                            @if ($response->sentiment)
                                <span class="badge text-bg-{{ $response->sentiment === 'positive' ? 'success' : ($response->sentiment === 'negative' ? 'danger' : 'secondary') }}">{{ ucfirst($response->sentiment) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $response->started_at?->diffForHumans() }}</td>
                        <td><button type="button" class="btn btn-sm btn-outline-primary" data-view-response="{{ $response->id }}">View</button></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No responses in this range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
