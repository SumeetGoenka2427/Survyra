<div class="row g-3 mb-4">
    <!-- Total Responses -->
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="ds-kpi-card">
            <div class="d-flex align-items-center gap-3">
                <div class="ds-kpi-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-inboxes"></i>
                </div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Total Responses</div>
                    <div class="fw-bold fs-4">{{ number_format($data['summary']['total_responses']) }}</div>
                    @if ($data['summary']['response_growth'] != 0)
                        <span class="ds-kpi-trend {{ $data['summary']['response_growth'] > 0 ? 'up' : 'down' }}">
                            <i class="bi bi-{{ $data['summary']['response_growth'] > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                            {{ abs($data['summary']['response_growth']) }}%
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Completion Rate -->
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="ds-kpi-card">
            <div class="d-flex align-items-center gap-3">
                <div class="ds-kpi-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Completion</div>
                    <div class="fw-bold fs-4">{{ $data['summary']['completion_rate'] }}%</div>
                    <span class="text-muted small">{{ number_format($data['summary']['completed_responses']) }} / {{ number_format($data['summary']['total_responses']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Surveys -->
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="ds-kpi-card">
            <div class="d-flex align-items-center gap-3">
                <div class="ds-kpi-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-ui-checks"></i>
                </div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Active Surveys</div>
                    <div class="fw-bold fs-4">{{ $data['summary']['active_surveys'] }}</div>
                    <span class="text-muted small">{{ $data['summary']['total_surveys'] }} total</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quality Score -->
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="ds-kpi-card">
            <div class="d-flex align-items-center gap-3">
                <div class="ds-kpi-icon bg-purple bg-opacity-10 text-purple" style="color: #8b5cf6;">
                    <i class="bi bi-stars"></i>
                </div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Quality Score</div>
                    <div class="fw-bold fs-4">{{ $data['summary']['quality_score'] }}%</div>
                    <span class="text-muted small">Response quality</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Contacts -->
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="ds-kpi-card">
            <div class="d-flex align-items-center gap-3">
                <div class="ds-kpi-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-people"></i>
                </div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Contacts</div>
                    <div class="fw-bold fs-4">{{ number_format($data['summary']['total_contacts']) }}</div>
                    <span class="text-muted small">{{ $data['summary']['consented_contacts'] }} consented</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Avg Daily -->
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
        <div class="ds-kpi-card">
            <div class="d-flex align-items-center gap-3">
                <div class="ds-kpi-icon bg-rose bg-opacity-10 text-rose" style="color: #f43f5e;">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Avg. Daily</div>
                    <div class="fw-bold fs-4">{{ $data['summary']['avg_daily_responses'] }}</div>
                    <span class="text-muted small">responses/day</span>
                </div>
            </div>
        </div>
    </div>
</div>