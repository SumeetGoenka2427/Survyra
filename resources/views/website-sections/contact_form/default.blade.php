<section class="ws-section ws-section-alt">
    <div class="container" style="max-width: 560px;">
        <div class="ws-card p-4 p-md-5" style="box-shadow: var(--ws-shadow);">
            <div class="text-center mb-4">
                @if($content['heading'] ?? null)
                    <h2 class="fw-bold mb-2" style="font-size: clamp(1.4rem, 3vw, 1.9rem);">{{ $content['heading'] }}</h2>
                @endif
                @if($content['intro'] ?? null)
                    <p class="mb-0" style="color: var(--ws-muted);">{{ $content['intro'] }}</p>
                @endif
            </div>

            @include('website-sections.contact_form._form-fields', ['content' => $content, 'sectionId' => $sectionId ?? null, 'pageId' => $pageId ?? null, 'contactAction' => $contactAction ?? null, 'isPreview' => $isPreview ?? false])
        </div>
    </div>
</section>
