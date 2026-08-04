@php
    $initials = function (string $name) {
        $parts = array_filter(explode(' ', trim($name)));
        $letters = array_map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)), array_slice($parts, 0, 2));
        return implode('', $letters) ?: '?';
    };
@endphp
<section class="ws-section ws-section-alt">
    <div class="container">
        <div class="row g-4">
            @foreach($content['items'] ?? [] as $item)
                <div class="col-md-4">
                    <div class="ws-card h-100 p-4 position-relative" style="box-shadow: var(--ws-shadow-sm);">
                        <i class="bi bi-quote" style="font-size: 1.75rem; color: rgba(var(--ws-primary-rgb), 0.35);"></i>
                        <p class="mb-4" style="color: var(--ws-text);">{{ $item['quote'] ?? '' }}</p>
                        <div class="d-flex align-items-center gap-2 mt-auto">
                            <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; border-radius: 50%; background: rgba(var(--ws-primary-rgb), 0.15); color: var(--ws-primary); font-weight: 700; font-size: 0.85rem;">
                                {{ $initials($item['author'] ?? '') }}
                            </div>
                            <div>
                                <div class="fw-semibold small">{{ $item['author'] ?? '' }}</div>
                                @if($item['role'] ?? null)
                                    <div class="small" style="color: var(--ws-muted);">{{ $item['role'] }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
