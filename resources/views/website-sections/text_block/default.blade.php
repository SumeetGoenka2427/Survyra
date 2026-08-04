<section class="ws-section">
    <div class="container" style="max-width: 760px;">
        @if($content['heading'] ?? null)
            <h2 class="fw-bold text-center mb-4" style="font-size: clamp(1.5rem, 3vw, 2.1rem);">{{ $content['heading'] }}</h2>
        @endif
        <div class="lh-lg" style="font-size: 1.05rem; color: var(--ws-muted); white-space: pre-line;">{{ $content['body'] ?? '' }}</div>
    </div>
</section>
