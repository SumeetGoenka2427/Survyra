<x-portal-layout title="Slack Integration">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center mb-4">
                <i class="bi bi-slack fs-1 me-3 text-muted"></i>
                <div>
                    <h5 class="mb-1">Slack Notifications</h5>
                    <p class="text-muted mb-0 small">Receive survey notifications directly in your Slack workspace.</p>
                </div>
            </div>

            @if ($integration)
                <div class="alert alert-info d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                        Slack connected
                        @if ($integration->channel)
                            to <strong>#{{ $integration->channel }}</strong>
                        @endif
                    </span>
                    <form method="POST" action="{{ route('portal.integrations.slack.destroy') }}" onsubmit="return confirm('Remove Slack integration?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Disconnect</button>
                    </form>
                </div>
                <div class="mt-3">
                    <h6 class="small fw-semibold">Subscribed Events</h6>
                    <div class="d-flex gap-2">
                        @foreach ($integration->events as $event)
                            <span class="badge text-bg-light text-dark border">{{ str_replace('_', ' ', $event) }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#editSlackModal">Edit settings</a>
                    </small>
                </div>
            @endif

            @if (!$integration || Request::has('edit'))
                <form method="POST" action="{{ route('portal.integrations.slack.store') }}" class="row g-3">
                    @csrf

                    <div class="col-md-8">
                        <label class="form-label small">Webhook URL <span class="text-danger">*</span></label>
                        <input type="url" name="webhook_url" class="form-control @error('webhook_url') is-invalid @enderror"
                               placeholder="https://hooks.slack.com/services/..."
                               value="{{ old('webhook_url', $integration?->webhook_url) }}" required>
                        @error('webhook_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text small">
                            Create a webhook in Slack: Apps &rarr; Incoming Webhooks &rarr; Add Configuration
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small">Channel (optional)</label>
                        <input type="text" name="channel" class="form-control"
                               placeholder="general" value="{{ old('channel', $integration?->channel) }}">
                        <div class="form-text small">Without the # prefix</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small">Notify me when...</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="events[]" value="negative_feedback" id="ev-negative"
                                        {{ old('events', $integration?->events ?? ['negative_feedback']) && in_array('negative_feedback', old('events', $integration?->events ?? ['negative_feedback'])) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="ev-negative">Negative feedback received</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="events[]" value="response_completed" id="ev-completed"
                                        {{ old('events', $integration?->events ?? []) && in_array('response_completed', old('events', $integration?->events ?? [])) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="ev-completed">Response completed</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="events[]" value="survey_published" id="ev-published"
                                        {{ old('events', $integration?->events ?? []) && in_array('survey_published', old('events', $integration?->events ?? [])) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="ev-published">Survey published</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            {{ $integration ? 'Update Settings' : 'Connect Slack' }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-portal-layout>