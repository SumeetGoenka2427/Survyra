@php $canCreateClient = auth()->user()->can('create', \App\Models\Client::class); @endphp

<div class="row g-3 mb-4">
    <div class="col-md-3"><x-stat-card label="Total Clients" :value="$stats['total']" icon="bi-buildings" color="primary" /></div>
    <div class="col-md-3"><x-stat-card label="Active" :value="$stats['active']" icon="bi-check-circle" color="success" /></div>
    <div class="col-md-3"><x-stat-card label="Trial" :value="$stats['trial']" icon="bi-hourglass-split" color="warning" /></div>
    <div class="col-md-3"><x-stat-card label="Inactive" :value="$stats['inactive']" icon="bi-pause-circle" color="secondary" /></div>
</div>

<div class="card border-0">
    @if ($clients->isEmpty())
        <x-ds-empty-state
            icon="bi-buildings"
            title="No clients found"
            description="Try adjusting your search or filters, or add your first client."
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
                        <th>Plan</th>
                        <th>Surveys</th>
                        <th>Campaigns</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clients as $client)
                        <tr class="ds-fade-in">
                            <td>
                                <a href="{{ route('admin.clients.edit', $client) }}" class="text-decoration-none fw-semibold">
                                    {{ $client->company_name }}
                                </a>
                            </td>
                            <td class="text-muted">{{ $client->industry ?? '—' }}</td>
                            <td class="text-muted">{{ $client->subscriptionPlan?->name ?? '—' }}</td>
                            <td class="text-muted small">{{ $client->surveys_count }}</td>
                            <td class="text-muted small">{{ $client->campaigns_count }}</td>
                            <td>
                                <span class="badge text-bg-{{ $client->status === 'active' ? 'success' : ($client->status === 'trial' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($client->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.clients.analytics', $client) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-graph-up"></i>
                                </a>
                                <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-warning"
                                    data-toggle-client-status
                                    data-url="{{ route('admin.clients.toggle-status', $client) }}"
                                >{{ $client->status === 'active' ? 'Deactivate' : 'Activate' }}</button>
                                @can('delete', $client)
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        data-delete-client
                                        data-url="{{ route('admin.clients.destroy', $client) }}"
                                    >Delete</button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white clients-pagination">
            {{ $clients->links() }}
        </div>
    @endif
</div>
