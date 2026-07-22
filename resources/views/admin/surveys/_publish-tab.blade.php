<div class="row g-4">

    {{-- ── PUBLISH / STATUS ── --}}
    <div class="col-md-6">
        <div class="card border-0 bg-light h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="bi bi-rocket-takeoff me-2"></i>Status & Publishing</h6>

                <p><strong>Status:</strong>
                    <span class="badge text-bg-{{ $survey->status === 'published' ? 'success' : ($survey->status === 'draft' ? 'warning' : 'secondary') }}">
                        {{ ucfirst($survey->status) }}
                    </span>
                </p>

                @if ($survey->status === 'published')
                    <div class="mb-3">
                        <label class="form-label small text-muted">Public Link</label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm" readonly value="{{ url('/s/'.$survey->slug) }}" id="survey-public-link">
                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="navigator.clipboard.writeText(document.getElementById('survey-public-link').value).then(()=>this.textContent='Copied!')">Copy</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Embed Code (iframe)</label>
                        <div class="input-group">
                            <textarea class="form-control form-control-sm font-monospace" rows="3" readonly id="survey-embed-code">&lt;iframe src="{{ url('/s/'.$survey->slug) }}" width="100%" height="600" frameborder="0" style="border:none;"&gt;&lt;/iframe&gt;</textarea>
                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="navigator.clipboard.writeText(document.getElementById('survey-embed-code').value).then(()=>this.textContent='Copied!')">Copy</button>
                        </div>
                    </div>

                    <a href="{{ route('admin.surveys.qr', $survey) }}" class="btn btn-outline-primary btn-sm mb-3">
                        <i class="bi bi-qr-code"></i> Download QR Code
                    </a>

                    <form action="{{ route('admin.surveys.archive', $survey) }}" method="POST" onsubmit="return confirm('Archive this survey?');">
                        @csrf
                        <button class="btn btn-outline-secondary btn-sm">Archive Survey</button>
                    </form>
                @elseif ($survey->status === 'draft')
                    <form action="{{ route('admin.surveys.publish', $survey) }}" method="POST">
                        @csrf
                        <button class="btn btn-success">
                            <i class="bi bi-rocket-takeoff"></i> Publish Survey
                        </button>
                    </form>
                @else
                    <p class="text-muted small">This survey is archived.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ── SURVEY SETTINGS FORM ── --}}
    <div class="col-md-6">
        <div class="card border-0 bg-light h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="bi bi-sliders me-2"></i>Survey Settings</h6>

                <form method="POST" action="{{ route('admin.surveys.update', $survey) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="title" value="{{ $survey->title }}">
                    <input type="hidden" name="theme_id" value="{{ $survey->theme_id }}">

                    {{-- Expiry --}}
                    <div class="mb-3">
                        <label class="form-label small">Expiry Date <span class="text-muted">(optional)</span></label>
                        <input type="datetime-local" name="expires_at" class="form-control form-control-sm"
                            value="{{ $survey->expires_at?->format('Y-m-d\TH:i') }}">
                        <div class="form-text">Survey stops accepting responses after this date.</div>
                    </div>

                    {{-- Max responses --}}
                    <div class="mb-3">
                        <label class="form-label small">Max Responses <span class="text-muted">(optional)</span></label>
                        <input type="number" name="max_responses" class="form-control form-control-sm" min="1"
                            value="{{ $survey->max_responses }}" placeholder="Unlimited">
                        <div class="form-text">Survey closes after this many completed responses.</div>
                    </div>

                    {{-- Anonymous --}}
                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="is_anonymous" value="0">
                        <input class="form-check-input" type="checkbox" name="is_anonymous" value="1" id="is_anonymous" @checked($survey->is_anonymous)>
                        <label class="form-check-label" for="is_anonymous">Anonymous mode <span class="text-muted small">(don't collect IP, device, or contact info)</span></label>
                    </div>

                    {{-- GDPR --}}
                    <div class="form-check form-switch mb-2">
                        <input type="hidden" name="gdpr_enabled" value="0">
                        <input class="form-check-input" type="checkbox" name="gdpr_enabled" value="1" id="gdpr_enabled" @checked($survey->gdpr_enabled)>
                        <label class="form-check-label" for="gdpr_enabled">GDPR consent checkbox</label>
                    </div>
                    <div class="mb-3 ms-4">
                        <input type="text" name="gdpr_text" class="form-control form-control-sm mb-1"
                            placeholder="I agree to the privacy policy and consent to data processing."
                            value="{{ $survey->gdpr_text }}">
                        <input type="url" name="privacy_policy_url" class="form-control form-control-sm"
                            placeholder="https://yoursite.com/privacy" value="{{ $survey->privacy_policy_url }}">
                    </div>

                    {{-- Tracking --}}
                    <div class="mb-2">
                        <label class="form-label small">Google Analytics ID <span class="text-muted">(e.g. G-XXXXXXXXXX)</span></label>
                        <input type="text" name="ga_tracking_id" class="form-control form-control-sm" value="{{ $survey->ga_tracking_id }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Meta Pixel ID</label>
                        <input type="text" name="meta_pixel_id" class="form-control form-control-sm" value="{{ $survey->meta_pixel_id }}">
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">Save Settings</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── WELCOME SCREEN ── --}}
    <div class="col-12">
        <div class="card border-0 bg-light">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i class="bi bi-play-circle me-2"></i>Welcome Screen <span class="text-muted small fw-normal">(shown before the first question)</span></h6>

                <form method="POST" action="{{ route('admin.surveys.update', $survey) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="title" value="{{ $survey->title }}">
                    <input type="hidden" name="theme_id" value="{{ $survey->theme_id }}">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small">Title <span class="text-muted">(optional)</span></label>
                            <input type="text" name="welcome_screen[title]" class="form-control form-control-sm"
                                value="{{ $survey->welcome_screen['title'] ?? '' }}"
                                placeholder="Welcome!">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">Description</label>
                            <input type="text" name="welcome_screen[description]" class="form-control form-control-sm"
                                value="{{ $survey->welcome_screen['description'] ?? '' }}"
                                placeholder="This survey takes about 2 minutes.">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Button Text</label>
                            <input type="text" name="welcome_screen[button_text]" class="form-control form-control-sm"
                                value="{{ $survey->welcome_screen['button_text'] ?? '' }}"
                                placeholder="Start Survey">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary btn-sm">Save Welcome Screen</button>
                        @if(!empty($survey->welcome_screen['title']))
                            <span class="text-success small ms-2"><i class="bi bi-check-circle"></i> Welcome screen active</span>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
