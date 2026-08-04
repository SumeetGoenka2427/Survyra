@php
    // Split on line breaks (a natural break point in seeded/entered copy) rather
    // than by character count, so each column reads as complete thoughts/items,
    // not a sentence sliced mid-word.
    $lines = collect(preg_split('/\r\n|\r|\n/', trim($content['body'] ?? '')))->filter(fn ($l) => $l !== '')->values();
    $mid = (int) ceil($lines->count() / 2);
    $left = $lines->slice(0, $mid);
    $right = $lines->slice($mid);
@endphp
<section class="ws-section ws-section-alt">
    <div class="container">
        @if($content['heading'] ?? null)
            <h2 class="fw-bold text-center mb-5" style="font-size: clamp(1.5rem, 3vw, 2.1rem);">{{ $content['heading'] }}</h2>
        @endif
        <div class="row g-4">
            <div class="col-md-6">
                <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                    @foreach ($left as $line)
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill mt-1" style="color: var(--ws-primary);"></i>
                            <span style="color: var(--ws-muted);">{{ $line }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                    @foreach ($right as $line)
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill mt-1" style="color: var(--ws-primary);"></i>
                            <span style="color: var(--ws-muted);">{{ $line }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>
