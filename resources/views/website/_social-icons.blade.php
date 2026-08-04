@php
    $platforms = \App\Models\Website::socialPlatforms();
    $links = array_filter($socialLinks ?? []);
@endphp
@if (! empty($links))
    <div class="d-flex gap-2 ws-social-icons">
        @foreach ($links as $key => $url)
            @continue(empty($url) || ! isset($platforms[$key]))
            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $platforms[$key]['label'] }}" class="ws-social-icon">
                <i class="bi {{ $platforms[$key]['icon'] }}" aria-hidden="true"></i>
            </a>
        @endforeach
    </div>
@endif
