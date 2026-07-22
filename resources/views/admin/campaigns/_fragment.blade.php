@if ($campaigns->isEmpty())
    <x-ds-empty-state
        icon="bi-megaphone"
        title="No campaigns found"
        description="Try a different client filter, or launch your first campaign."
        action-label="New Campaign"
        :action-url="route('admin.campaigns.create')"
    />
@else
    <div class="row g-3">
        @foreach ($campaigns as $campaign)
            @php
                $stats = collect($campaign->stats ?? []);
                $total = max($campaign->recipients_count ?? 0, 1);
                $sentPct = round((($stats->get('sent', 0) + $stats->get('delivered', 0)) / $total) * 100);
                $failedCount = $stats->get('failed', 0);
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 ds-hover h-100 ds-fade-in">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge text-bg-{{ $campaign->status === 'completed' ? 'success' : ($campaign->status === 'sending' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($campaign->status) }}
                            </span>
                            <span class="badge text-bg-light text-dark border">{{ strtoupper($campaign->type) }}</span>
                        </div>

                        <h6 class="fw-bold mb-1">{{ $campaign->name }}</h6>
                        <div class="text-muted small mb-1">{{ $campaign->client->company_name }}</div>
                        <div class="text-muted small mb-3">{{ $campaign->survey->title }}</div>

                        <div class="mb-2">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>{{ $stats->get('sent', 0) + $stats->get('delivered', 0) }} / {{ $total }} sent</span>
                                @if ($failedCount > 0)
                                    <span class="text-danger">{{ $failedCount }} failed</span>
                                @endif
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" style="width: {{ $sentPct }}%;"></div>
                            </div>
                        </div>

                        <div class="mt-auto d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.campaigns.show', $campaign) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i> View
                            </a>
                            @if ($campaign->status === 'draft')
                                <button type="button" class="btn btn-sm btn-success" data-campaign-send data-url="{{ route('admin.campaigns.send', $campaign) }}">
                                    <i class="bi bi-send"></i> Send
                                </button>
                            @endif
                            @if ($failedCount > 0)
                                <button type="button" class="btn btn-sm btn-outline-warning" data-campaign-retry data-url="{{ route('admin.campaigns.retry', $campaign) }}">
                                    <i class="bi bi-arrow-repeat"></i> Retry
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-3 campaigns-pagination">
        {{ $campaigns->links() }}
    </div>
@endif
