<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-table me-2 text-primary"></i>Survey Performance</span>
        <span class="badge bg-light text-dark">{{ count($data['survey_performance']) }} surveys</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Survey</th>
                    <th>Status</th>
                    <th>Responses</th>
                    <th>Completed</th>
                    <th>Completion Rate</th>
                    <th>Progress</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['survey_performance'] as $survey)
                    <tr>
                        <td>
                            <div class="fw-medium">{{ $survey['title'] }}</div>
                            <div class="small text-muted">Created {{ $survey['created_at']?->diffForHumans() }}</div>
                        </td>
                        <td>
                            @php
                                $statusColors = ['published' => 'success', 'draft' => 'secondary', 'archived' => 'warning'];
                                $color = $statusColors[$survey['status']] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ ucfirst($survey['status']) }}</span>
                        </td>
                        <td class="fw-semibold">{{ number_format($survey['total_responses']) }}</td>
                        <td class="fw-semibold text-success">{{ number_format($survey['completed_responses']) }}</td>
                        <td>
                            <span class="fw-semibold">{{ $survey['completion_rate'] }}%</span>
                        </td>
                        <td style="min-width: 120px;">
                            @if ($survey['total_responses'] > 0)
                                <div class="progress" style="height: 6px; width: 100px;">
                                    <div class="progress-bar bg-success" style="width: {{ $survey['completion_rate'] }}%"></div>
                                </div>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.analytics.index', ['client_id' => $data['client']['id'], 'survey_id' => $survey['id']]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-graph-up"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox display-5 d-block mb-2"></i>
                            <p class="mb-0">No survey data in this period.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>