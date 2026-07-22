<x-portal-layout title="Webhooks">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><strong>Webhooks</strong></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><th>URL</th><th>Events</th><th>Status</th><th>Last Triggered</th><th></th></tr></thead>
                <tbody>
                    @forelse ($webhooks as $webhook)
                        <tr>
                            <td class="text-truncate" style="max-width:250px;">{{ $webhook->url }}</td>
                            <td>{{ implode(', ', $webhook->events) }}</td>
                            <td>
                                <span class="badge text-bg-{{ $webhook->is_active ? 'success' : 'danger' }}">
                                    {{ $webhook->is_active ? 'Active' : 'Disabled' }}
                                </span>
                                @if ($webhook->failure_count > 0)
                                    <span class="badge text-bg-warning ms-1">{{ $webhook->failure_count }} failures</span>
                                @endif
                            </td>
                            <td>{{ $webhook->last_triggered_at?->diffForHumans() ?? 'Never' }}</td>
                            <td>
                                <form method="POST" action="{{ route('portal.integrations.webhooks.destroy', $webhook) }}" onsubmit="return confirm('Delete this webhook?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No webhooks yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h6 class="mb-3">Add Webhook</h6>
            <form method="POST" action="{{ route('portal.integrations.webhooks.store') }}" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label small">Endpoint URL</label>
                    <input type="url" name="url" class="form-control @error('url') is-invalid @enderror" placeholder="https://your-app.com/webhook" required>
                    @error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Events</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="events[]" value="response.started" id="ev-started">
                        <label class="form-check-label" for="ev-started">response.started</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="events[]" value="response.completed" id="ev-completed" checked>
                        <label class="form-check-label" for="ev-completed">response.completed</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Secret (optional)</label>
                    <input type="text" name="secret" class="form-control" placeholder="Signing secret">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Add Webhook</button>
                </div>
            </form>
        </div>
    </div>
</x-portal-layout>
