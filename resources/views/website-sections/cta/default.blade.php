@php
    $buttonUrl = is_array($content['button_link'] ?? null) ? ($content['button_link']['url'] ?? '#') : ($content['button_link'] ?? '#');
    $isSecondary = ($content['variant'] ?? 'primary') === 'secondary';
@endphp
<section class="ws-section text-center position-relative overflow-hidden"
    style="background: linear-gradient(135deg, var({{ $isSecondary ? '--ws-secondary' : '--ws-primary' }}), var(--ws-primary-dark)); color: #fff;">
    <div class="position-absolute top-0 end-0" style="width: 260px; height: 260px; border-radius: 50%; background: rgba(255,255,255,0.08); filter: blur(50px); transform: translate(30%, -40%);"></div>
    <div class="container position-relative">
        <h2 class="fw-bold mb-4" style="font-size: clamp(1.5rem, 3.2vw, 2.2rem);">{{ $content['heading'] ?? '' }}</h2>
        @if($content['button_text'] ?? null)
            <a href="{{ $buttonUrl }}" class="btn btn-lg px-4 fw-semibold shadow-sm" style="background: #fff; color: var(--ws-primary); border-radius: var(--ws-btn-radius); border: none;">
                {{ $content['button_text'] }}
            </a>
        @endif
    </div>
</section>
