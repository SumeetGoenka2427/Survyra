<x-portal-layout title="API Keys">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if (session('new_key'))
        <div class="alert alert-warning">
            <strong>Copy your API key now — it will not be shown again:</strong><br>
            <code class="user-select-all">{{ session('new_key') }}</code>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><strong>API Keys</strong></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><th>Name</th><th>Last Used</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($keys as $key)
                        <tr>
                            <td>{{ $key->name }}</td>
                            <td>{{ $key->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                            <td><span class="badge text-bg-{{ $key->is_active ? 'success' : 'secondary' }}">{{ $key->is_active ? 'Active' : 'Revoked' }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('portal.integrations.api-keys.destroy', $key) }}" onsubmit="return confirm('Delete this key?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No API keys yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h6 class="mb-3">Create API Key</h6>
            <form method="POST" action="{{ route('portal.integrations.api-keys.store') }}" class="d-flex gap-2">
                @csrf
                <input type="text" name="name" class="form-control" placeholder="Key name (e.g. Zapier)" required style="max-width:300px;">
                <button type="submit" class="btn btn-primary">Create Key</button>
            </form>
        </div>
    </div>
</x-portal-layout>
