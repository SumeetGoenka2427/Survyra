<section class="ws-section">
    <div class="container">
        <div class="row g-0 ws-card overflow-hidden" style="box-shadow: var(--ws-shadow);">
            <div class="col-md-5 p-4 p-md-5 d-flex flex-column justify-content-center" style="background: linear-gradient(135deg, var(--ws-primary), var(--ws-primary-dark)); color: #fff;">
                @if($content['heading'] ?? null)
                    <h2 class="fw-bold mb-3" style="font-size: clamp(1.4rem, 3vw, 1.9rem);">{{ $content['heading'] }}</h2>
                @endif
                @if($content['intro'] ?? null)
                    <p class="mb-0" style="color: rgba(255,255,255,0.9);">{{ $content['intro'] }}</p>
                @endif
                <div class="d-flex align-items-center gap-2 mt-4">
                    <i class="bi bi-shield-check" aria-hidden="true"></i>
                    <span class="small">We typically reply within one business day.</span>
                </div>
            </div>
            <div class="col-md-7 p-4 p-md-5">
                @include('website-sections.contact_form._form-fields', ['content' => $content, 'sectionId' => $sectionId ?? null, 'pageId' => $pageId ?? null, 'contactAction' => $contactAction ?? null, 'isPreview' => $isPreview ?? false])
            </div>
        </div>
    </div>
</section>
