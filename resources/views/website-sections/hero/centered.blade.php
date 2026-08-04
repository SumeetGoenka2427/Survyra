@php
    $ctaUrl = is_array($content['cta_link'] ?? null) ? ($content['cta_link']['url'] ?? '#') : ($content['cta_link'] ?? '#');
    $hasImage = ! empty($content['background_image']);
    $headingTag = $headingTag ?? 'h1';
@endphp
<section class="ws-hero-centered position-relative text-center overflow-hidden"
    style="
        padding: clamp(4.5rem, 10vw, 8rem) 0;
        @if($hasImage)
            background: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.55)), url('{{ $content['background_image'] }}') center/cover no-repeat;
            color: #fff;
        @else
            background:
                radial-gradient(circle at 15% 20%, rgba(var(--ws-primary-rgb), 0.16), transparent 45%),
                radial-gradient(circle at 90% 15%, rgba(var(--ws-secondary-rgb), 0.14), transparent 40%);
        @endif
    ">
    <div class="container position-relative" style="z-index: 2;">
        @if($headingTag === 'h1')
            <h1 class="fw-bold ws-fade-up mx-auto" style="font-size: clamp(2rem, 5vw, 3.25rem); line-height: 1.15; max-width: 760px; @if($hasImage) color: #fff; @endif">
                {{ $content['heading'] ?? '' }}
            </h1>
        @else
            <h2 class="fw-bold ws-fade-up mx-auto" style="font-size: clamp(2rem, 5vw, 3.25rem); line-height: 1.15; max-width: 760px; @if($hasImage) color: #fff; @endif">
                {{ $content['heading'] ?? '' }}
            </h2>
        @endif
        @if($content['subheading'] ?? null)
            <p class="ws-fade-up ws-fade-up-delay-1 mx-auto mt-3" style="font-size: 1.15rem; max-width: 640px; @if($hasImage) color: rgba(255,255,255,0.9); @else color: var(--ws-muted); @endif">
                {{ $content['subheading'] }}
            </p>
        @endif
        @if($content['cta_text'] ?? null)
            <div class="ws-fade-up ws-fade-up-delay-2 mt-4">
                <a href="{{ $ctaUrl }}" class="btn btn-primary btn-lg px-4 shadow-sm">{{ $content['cta_text'] }}</a>
            </div>
        @endif
    </div>
</section>
