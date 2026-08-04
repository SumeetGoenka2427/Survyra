@php
    $buttonUrl = is_array($content['button_link'] ?? null) ? ($content['button_link']['url'] ?? '#') : ($content['button_link'] ?? '#');
    $isSecondary = ($content['variant'] ?? 'primary') === 'secondary';
@endphp
<section class="ws-section">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-4 p-md-5"
            style="border: 2px solid var({{ $isSecondary ? '--ws-secondary' : '--ws-primary' }}); border-radius: var(--ws-radius);">
            <h2 class="fw-bold mb-0" style="font-size: clamp(1.3rem, 2.8vw, 1.8rem);">{{ $content['heading'] ?? '' }}</h2>
            @if($content['button_text'] ?? null)
                <a href="{{ $buttonUrl }}" class="btn btn-{{ $isSecondary ? 'secondary' : 'primary' }} btn-lg px-4 flex-shrink-0">
                    {{ $content['button_text'] }}
                </a>
            @endif
        </div>
    </div>
</section>
