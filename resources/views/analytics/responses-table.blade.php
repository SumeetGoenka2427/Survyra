@php
    $sortLink = fn (string $column, string $label) => sprintf(
        '<a href="%s" data-sort-link class="text-decoration-none text-reset d-inline-flex align-items-center gap-1">%s <i class="bi bi-arrow-%s small text-muted"></i></a>',
        route('admin.analytics.responses.index', array_merge(request()->query(), ['sort' => $column, 'dir' => ($sort === $column && $direction === 'asc') ? 'desc' : 'asc'])),
        $label,
        $sort === $column ? ($direction === 'asc' ? 'up' : 'down') : 'down-up'
    );
@endphp

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Survey</th>
                    <th>Contact</th>
                    <th>{!! $sortLink('status', 'Status') !!}</th>
                    <th>Sentiment</th>
                    <th>{!! $sortLink('score', 'Score') !!}</th>
                    <th>Source</th>
                    <th>{!! $sortLink('started_at', 'Started') !!}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($responses as $response)
                    <tr>
                        <td>{{ $response->survey->title }}</td>
                        <td class="text-muted small">{{ $response->contact?->name ?? 'Anonymous' }}</td>
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
                    <tr><td colspan="8" class="text-center text-muted py-4">No responses match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3 analytics-pagination">
    {{ $responses->links() }}
</div>
