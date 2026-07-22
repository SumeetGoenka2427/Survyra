@php
    $stats = collect($campaign->stats ?? []);
@endphp
<x-admin-layout :title="$campaign->name">
    <div class="row g-3 mb-4">
        <div class="col-md-3"><x-stat-card label="Total Recipients" :value="$campaign->recipients->count()" icon="bi-people" color="primary" /></div>
        <div class="col-md-3"><x-stat-card label="Sent" :value="$stats->get('sent', 0) + $stats->get('delivered', 0)" icon="bi-send" color="success" /></div>
        <div class="col-md-3"><x-stat-card label="Failed" :value="$stats->get('failed', 0)" icon="bi-x-circle" color="danger" /></div>
        <div class="col-md-3"><x-stat-card label="Clicked" :value="$campaign->recipients->whereNotNull('clicked_at')->count()" icon="bi-cursor" color="warning" /></div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $campaign->survey->title }}</strong> &middot;
                <span class="badge text-bg-light text-dark border">{{ strtoupper($campaign->type) }}</span>
                <span class="badge text-bg-{{ $campaign->status === 'completed' ? 'success' : ($campaign->status === 'sending' ? 'warning' : 'secondary') }}">
                    {{ ucfirst($campaign->status) }}
                </span>
            </div>
            <div class="d-flex gap-2">
                @if ($campaign->status === 'draft')
                    <form action="{{ route('admin.campaigns.send', $campaign) }}" method="POST">
                        @csrf
                        <button class="btn btn-success btn-sm"><i class="bi bi-send"></i> Send Campaign</button>
                    </form>
                @endif
                @if ($stats->get('failed', 0) > 0)
                    <form action="{{ route('admin.campaigns.retry', $campaign) }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-warning btn-sm"><i class="bi bi-arrow-repeat"></i> Retry Failed</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><strong>Recipients</strong></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Sent</th>
                        <th>Clicked</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($campaign->recipients as $recipient)
                        <tr>
                            <td>{{ $recipient->contact->name }}</td>
                            <td>
                                <span class="badge text-bg-{{ $recipient->status === 'sent' || $recipient->status === 'delivered' ? 'success' : ($recipient->status === 'failed' ? 'danger' : 'secondary') }}">
                                    {{ ucfirst($recipient->status) }}
                                </span>
                            </td>
                            <td>{{ $recipient->sent_at?->diffForHumans() ?? '—' }}</td>
                            <td>{{ $recipient->clicked_at ? 'Yes' : 'No' }}</td>
                            <td class="text-danger small">{{ $recipient->error_message }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No recipients.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
