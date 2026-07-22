<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-activity me-2 text-primary"></i>Recent Activity</span>
        <span class="badge bg-light text-dark">{{ count($data['recent_activities']) }} activities</span>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse ($data['recent_activities'] as $activity)
                <div class="list-group-item border-0 d-flex align-items-start gap-3 py-3 px-4">
                    <div class="d-flex flex-column align-items-center" style="min-width: 40px;">
                        @php
                            $sentimentColors = [
                                'positive' => ['bg' => 'bg-success', 'icon' => 'bi-emoji-smile'],
                                'negative' => ['bg' => 'bg-danger', 'icon' => 'bi-emoji-frown'],
                                'neutral' => ['bg' => 'bg-secondary', 'icon' => 'bi-emoji-neutral'],
                            ];
                            $defaultSentiment = ['bg' => 'bg-light', 'icon' => 'bi-chat-dots'];
                            $sentimentMeta = $sentimentColors[$activity['sentiment']] ?? $defaultSentiment;
                            $statusColor = $activity['status'] === 'completed' ? 'success' : ($activity['status'] === 'abandoned' ? 'danger' : 'warning');
                        @endphp
                        <div class="d-flex align-items-center justify-content-center rounded-circle {{ $sentimentMeta['bg'] }} bg-opacity-10" style="width: 36px; height: 36px; color: var(--bs-{{ $statusColor }});">
                            <i class="bi {{ $sentimentMeta['icon'] }}"></i>
                        </div>
                        <div class="vr mt-1" style="height: 20px;"></div>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="fw-medium">{{ $activity['survey_title'] }}</span>
                                <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }} ms-2" style="font-size: 0.65rem;">
                                    {{ ucfirst($activity['status']) }}
                                </span>
                            </div>
                            <span class="small text-muted" style="white-space: nowrap;">{{ $activity['started_at']?->diffForHumans() }}</span>
                        </div>
                        <div class="small text-muted mt-1">
                            <span>{{ $activity['contact_name'] }}</span>
                            @if ($activity['source'])
                                <span class="mx-1">·</span>
                                <span>{{ ucfirst($activity['source']) }}</span>
                            @endif
                            @if ($activity['device'])
                                <span class="mx-1">·</span>
                                <span>{{ ucfirst($activity['device']) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox display-5 d-block mb-2"></i>
                    <p class="mb-0">No recent activity in this period.</p>
                </div>
            @endforelse
        </div>
    </div>
    @if (count($data['recent_activities']) > 0)
        <div class="card-footer bg-transparent text-center border-0">
            <a href="{{ route('admin.analytics.responses.index', ['client_id' => $data['client']['id']]) }}" class="btn btn-sm btn-outline-primary">
                View All Responses <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    @endif
</div>