@php
    $fieldIcon = fn (string $type) => match ($type) {
        'email' => 'bi-envelope',
        'tel' => 'bi-telephone',
        'textarea' => 'bi-chat-left-text',
        default => 'bi-person',
    };
    $isPreview = $isPreview ?? false;
@endphp
@if(session('website_contact_sent'))
    <div class="text-center py-3" role="status">
        <div class="d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px; border-radius: 50%; background: rgba(34,197,94,0.12); color: #16a34a; font-size: 1.6rem;">
            <i class="bi bi-check-lg" aria-hidden="true"></i>
        </div>
        <p class="fw-semibold mb-0">Thanks! Your message has been sent.</p>
    </div>
@else
    @if($isPreview)
        <div class="alert alert-secondary small mb-3" role="note">
            <i class="bi bi-eye" aria-hidden="true"></i> Preview only - form submission is disabled here.
        </div>
    @endif
    <form method="POST" action="{{ $isPreview ? '#' : (isset($contactAction) ? $contactAction : '#') }}" @if($isPreview) onsubmit="return false;" @endif>
        @csrf
        <input type="hidden" name="section_id" value="{{ $sectionId ?? '' }}">
        <input type="hidden" name="page_id" value="{{ $pageId ?? '' }}">
        <input type="text" name="company_website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;">
        @foreach($content['fields'] ?? [] as $field)
            <div class="mb-3">
                <label class="form-label" for="ws-field-{{ $field['key'] }}">{{ $field['label'] }}</label>
                <div class="input-group">
                    <span class="input-group-text" style="background: var(--ws-card-bg); border-color: var(--ws-border); color: var(--ws-muted);">
                        <i class="bi {{ $fieldIcon($field['type'] ?? 'text') }}" aria-hidden="true"></i>
                    </span>
                    @if(($field['type'] ?? 'text') === 'textarea')
                        <textarea class="form-control" id="ws-field-{{ $field['key'] }}" name="{{ $field['key'] }}" rows="4" @if($field['required'] ?? false) required aria-required="true" @endif @disabled($isPreview)></textarea>
                    @else
                        <input type="{{ $field['type'] ?? 'text' }}" class="form-control" id="ws-field-{{ $field['key'] }}" name="{{ $field['key'] }}" @if($field['required'] ?? false) required aria-required="true" @endif @disabled($isPreview)>
                    @endif
                </div>
            </div>
        @endforeach
        <button type="submit" class="btn btn-primary w-100 mt-2" @disabled($isPreview)>Send Message</button>
    </form>
@endif
