<section class="ws-section">
    <div class="container">
        <div class="row g-3">
            @foreach($content['images'] ?? [] as $image)
                <div class="col-md-4 col-6">
                    <div class="ws-gallery-zoom position-relative" style="border-radius: var(--ws-radius); box-shadow: var(--ws-shadow-sm); aspect-ratio: 4/3;">
                        <img src="{{ $image['image_path'] ?? '' }}" alt="{{ $image['caption'] ?: 'Photo' }}" loading="lazy" class="w-100 h-100" style="object-fit: cover;">
                        @if($image['caption'] ?? null)
                            <div class="position-absolute bottom-0 start-0 end-0 px-3 py-2" style="background: linear-gradient(transparent, rgba(0,0,0,0.65)); pointer-events: none;">
                                <span class="small fw-semibold text-white">{{ $image['caption'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
