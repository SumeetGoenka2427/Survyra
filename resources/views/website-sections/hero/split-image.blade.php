@php
    $ctaUrl = is_array($content['cta_link'] ?? null) ? ($content['cta_link']['url'] ?? '#') : ($content['cta_link'] ?? '#');
    $headingTag = $headingTag ?? 'h1';
@endphp
<section class="ws-section position-relative overflow-hidden">
    <div class="position-absolute top-0 start-0" style="width: 320px; height: 320px; border-radius: 50%; background: rgba(var(--ws-primary-rgb), 0.12); filter: blur(60px); transform: translate(-40%, -40%);"></div>
    <div class="container position-relative">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 ws-fade-up">
                @if($headingTag === 'h1')
                    <h1 class="fw-bold" style="font-size: clamp(1.8rem, 4vw, 2.85rem); line-height: 1.15;">{{ $content['heading'] ?? '' }}</h1>
                @else
                    <h2 class="fw-bold" style="font-size: clamp(1.8rem, 4vw, 2.85rem); line-height: 1.15;">{{ $content['heading'] ?? '' }}</h2>
                @endif
                @if($content['subheading'] ?? null)
                    <p class="mt-3" style="font-size: 1.1rem; color: var(--ws-muted);">{{ $content['subheading'] }}</p>
                @endif
                @if($content['cta_text'] ?? null)
                    <a href="{{ $ctaUrl }}" class="btn btn-primary btn-lg px-4 mt-3 shadow-sm">{{ $content['cta_text'] }}</a>
                @endif
            </div>
            <div class="col-lg-6 ws-fade-up ws-fade-up-delay-1">
                @if($content['background_image'] ?? null)
                    <div class="ws-card p-2" style="box-shadow: var(--ws-shadow);">
                        <img src="{{ $content['background_image'] }}" alt="{{ $content['heading'] ?? '' }}" loading="eager" class="img-fluid w-100" style="border-radius: calc(var(--ws-radius) * 0.7); aspect-ratio: 4/3; object-fit: cover;">
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
