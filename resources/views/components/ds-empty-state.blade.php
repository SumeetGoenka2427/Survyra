@props(['icon' => 'bi-inbox', 'title', 'description' => null, 'actionLabel' => null, 'actionUrl' => null])
<div class="ds-empty-state ds-fade-in">
    <div class="ds-empty-icon">
        <i class="bi {{ $icon }}"></i>
    </div>
    <h5 class="mb-1">{{ $title }}</h5>
    @if ($description)
        <p class="text-muted mb-3 mx-auto" style="max-width: 420px;">{{ $description }}</p>
    @endif
    @if ($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>{{ $actionLabel }}
        </a>
    @endif
    {{ $slot }}
</div>
