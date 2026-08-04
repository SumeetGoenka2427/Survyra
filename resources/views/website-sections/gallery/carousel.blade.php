@php $carouselId = 'ws-gallery-'.($sectionId ?? uniqid()); @endphp
<section class="ws-section ws-section-alt">
    <div class="container" style="max-width: 860px;">
        <div id="{{ $carouselId }}" class="carousel slide position-relative" role="region" aria-roledescription="carousel" aria-label="Image gallery" style="border-radius: var(--ws-radius); overflow: hidden; box-shadow: var(--ws-shadow);">
            <div class="carousel-inner">
                @foreach($content['images'] ?? [] as $index => $image)
                    <div class="carousel-item @if($index === 0) active @endif" @if($index !== 0) aria-hidden="true" @endif>
                        <img src="{{ $image['image_path'] ?? '' }}" alt="{{ $image['caption'] ?: 'Photo' }}" loading="lazy" class="d-block w-100" style="aspect-ratio: 16/9; object-fit: cover;">
                        @if($image['caption'] ?? null)
                            <div class="carousel-caption" style="background: rgba(0,0,0,0.55); border-radius: calc(var(--ws-radius) * 0.5); bottom: 0.75rem; padding: 0.5rem 1rem;">
                                <p class="mb-0 fw-semibold small">{{ $image['caption'] }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            @if(count($content['images'] ?? []) > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev" aria-label="Previous slide">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next" aria-label="Next slide">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
                <div class="carousel-indicators">
                    @foreach($content['images'] ?? [] as $index => $image)
                        <button type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide-to="{{ $index }}" aria-label="Slide {{ $index + 1 }}" @if($index === 0) class="active" aria-current="true" @endif></button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
