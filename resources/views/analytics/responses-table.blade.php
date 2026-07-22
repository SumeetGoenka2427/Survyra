<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Survey</th>
                    <th>Status</th>
                    <th>Sentiment</th>
                    <th>Score</th>
                    <th>Source</th>
                    <th>Started</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($responses as $response)
                    <tr>
                        <td>{{ $response->survey->title }}</td>
                        <td><span class="badge text-bg-{{ $response->status === 'completed' ? 'success' : ($response->status === 'abandoned' ? 'secondary' : 'warning') }}">{{ ucfirst($response->status) }}</span></td>
                        <td>
                            @if ($response->sentiment)
                                <span class="badge text-bg-{{ $response->sentiment === 'positive' ? 'success' : ($response->sentiment === 'negative' ? 'danger' : 'secondary') }}">{{ ucfirst($response->sentiment) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $response->score ?? '—' }}</td>
                        <td class="text-muted small">{{ $response->source ? ucfirst($response->source) : '—' }}</td>
                        <td>{{ $response->started_at?->diffForHumans() }}</td>
                        <td><button type="button" class="btn btn-sm btn-outline-primary" data-view-response="{{ $response->id }}">View</button></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No responses match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3 analytics-pagination">
    {{ $responses->links() }}
</div>
