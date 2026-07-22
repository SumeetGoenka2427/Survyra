<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-body">
        <div class="row g-4 align-items-center">
            <div class="col-auto">
                <div class="ds-kpi-icon" style="background: {{ $data['client']['brand_color'] ? \App\Services\ColorService::hexToRgba($data['client']['brand_color'], 0.1) : '#eef2ff' }}; color: {{ $data['client']['brand_color'] ?? '#4f46e5' }}; width: 60px; height: 60px; font-size: 1.5rem;">
                    <i class="bi bi-buildings"></i>
                </div>
            </div>
            <div class="col">
                <h5 class="fw-bold mb-1">{{ $data['client']['company_name'] }}</h5>
                <div class="d-flex flex-wrap gap-3 small text-muted">
                    @if ($data['client']['industry'])
                        <span><i class="bi bi-tag me-1"></i>{{ $data['client']['industry'] }}</span>
                    @endif
                    @if ($data['client']['email'])
                        <span><i class="bi bi-envelope me-1"></i>{{ $data['client']['email'] }}</span>
                    @endif
                    @if ($data['client']['phone'])
                        <span><i class="bi bi-telephone me-1"></i>{{ $data['client']['phone'] }}</span>
                    @endif
                    @if ($data['client']['website'])
                        <span><i class="bi bi-globe me-1"></i><a href="{{ $data['client']['website'] }}" target="_blank" class="text-decoration-none">{{ $data['client']['website'] }}</a></span>
                    @endif
                    <span><i class="bi bi-calendar me-1"></i>Client since {{ $data['client']['created_at']?->format('M Y') }}</span>
                    <span>
                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem; color: {{ $data['client']['status'] === 'active' ? '#10b981' : ($data['client']['status'] === 'trial' ? '#f59e0b' : '#94a3b8') }}"></i>
                        {{ ucfirst($data['client']['status']) }}
                    </span>
                </div>
            </div>
            <div class="col-auto text-end">
                <div class="d-flex align-items-center gap-3">
                    <div class="text-center">
                        <div class="fw-bold fs-4 text-primary">{{ $data['summary']['total_surveys'] }}</div>
                        <div class="small text-muted">Total Surveys</div>
                    </div>
                    <div class="vr"></div>
                    <div class="text-center">
                        <div class="fw-bold fs-4 text-success">{{ $data['summary']['active_surveys'] }}</div>
                        <div class="small text-muted">Active</div>
                    </div>
                    <div class="vr"></div>
                    <div class="text-center">
                        <div class="fw-bold fs-4 text-info">{{ $data['summary']['total_responses'] }}</div>
                        <div class="small text-muted">Responses</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>