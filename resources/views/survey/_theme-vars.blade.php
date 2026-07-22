@php
    $primary = $theme?->primary_color ?? '#0d6efd';
    $secondary = $theme?->secondary_color ?? '#6c757d';
    $background = $theme?->background ?? '#f4f6f8';
    $font = $theme?->font ?? 'system-ui';
    $radius = $theme?->border_radius ?? 8;
    $buttonRadius = match ($theme?->button_style ?? 'rounded') {
        'pill' => '999px',
        'square' => '0',
        default => '6px',
    };
@endphp
<style>
    :root {
        --survey-primary: {{ $primary }};
        --survey-secondary: {{ $secondary }};
        --survey-bg: {{ $background }};
        --survey-font: '{{ $font }}', system-ui, sans-serif;
        --survey-radius: {{ $radius }}px;
        --survey-btn-radius: {{ $buttonRadius }};
        --survey-border: #dde2ea;
        --survey-text: #1e293b;
        --survey-muted: #64748b;
        --survey-accent: #f59e0b;
    }
    body { background: var(--survey-bg); font-family: var(--survey-font); color: var(--survey-text); }
    .btn-survyra-primary { background: var(--survey-primary); color: #fff; border-radius: var(--survey-btn-radius); border: none; }
    .btn-survyra-primary:hover { background: var(--survey-primary); opacity: 0.9; color: #fff; }
    .survey-card { border-radius: var(--survey-radius); }
    .progress-bar { background: var(--survey-primary); }
    .btn-check:checked + .btn-outline-primary { background: var(--survey-primary); border-color: var(--survey-primary); color: #fff; }
    {!! $theme?->custom_css ?? '' !!}
</style>
