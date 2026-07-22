@php $canCreateClient = auth()->user()->can('create', \App\Models\Client::class); @endphp
@if ($recentClients->isEmpty())
    <x-ds-empty-state
        icon="bi-buildings"
        title="No clients yet"
        description="Add your first client to start building surveys and collecting feedback."
        :action-label="$canCreateClient ? 'Add Client' : null"
        :action-url="$canCreateClient ? route('admin.clients.create') : null"
    />
@else
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Industry</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recentClients as $client)
                    <tr class="ds-fade-in">
                        <td>
                            <a href="{{ route('admin.clients.edit', $client) }}" class="text-decoration-none fw-semibold">
                                {{ $client->company_name }}
                            </a>
                        </td>
                        <td class="text-muted">{{ $client->industry ?? '—' }}</td>
                        <td><span class="badge text-bg-{{ $client->status === 'active' ? 'success' : ($client->status === 'trial' ? 'warning' : 'secondary') }}">{{ ucfirst($client->status) }}</span></td>
                        <td class="text-muted small">{{ $client->created_at->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
