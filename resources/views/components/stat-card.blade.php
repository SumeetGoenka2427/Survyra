@props(['label', 'value', 'icon' => 'bi-bar-chart', 'color' => 'primary', 'trend' => null])
<div class="ds-kpi-card h-100 ds-fade-in">
    <div class="d-flex align-items-start justify-content-between mb-2">
        <div class="ds-kpi-icon bg-{{ $color }}-subtle text-{{ $color }}">
            <i class="bi {{ $icon }}"></i>
        </div>
        @if ($trend)
            <span class="ds-kpi-trend {{ $trend['direction'] ?? 'flat' }}">
                <i class="bi bi-arrow-{{ ($trend['direction'] ?? 'flat') === 'up' ? 'up' : (($trend['direction'] ?? 'flat') === 'down' ? 'down' : 'right') }}-short"></i>
                {{ $trend['value'] ?? '' }}
            </span>
        @endif
    </div>
    <div class="text-muted small mb-1">{{ $label }}</div>
    <div class="fs-3 fw-bold">{{ $value }}</div>
</div>
