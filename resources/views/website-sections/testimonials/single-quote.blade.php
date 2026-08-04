@php $item = ($content['items'] ?? [])[0] ?? null; @endphp
@if($item)
    <section class="ws-section text-center">
        <div class="container" style="max-width: 700px;">
            <i class="bi bi-quote d-block mx-auto mb-2" style="font-size: 2.75rem; color: rgba(var(--ws-primary-rgb), 0.35);"></i>
            <p class="fw-medium" style="font-size: 1.35rem; line-height: 1.5;">&ldquo;{{ $item['quote'] ?? '' }}&rdquo;</p>
            <div class="mt-3">
                <div class="fw-semibold">{{ $item['author'] ?? '' }}</div>
                @if($item['role'] ?? null)
                    <div class="small" style="color: var(--ws-muted);">{{ $item['role'] }}</div>
                @endif
            </div>
        </div>
    </section>
@endif
