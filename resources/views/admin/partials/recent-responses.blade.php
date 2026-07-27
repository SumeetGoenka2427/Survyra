@if ($recentResponses->isEmpty())
    <x-ds-empty-state
        icon="bi-inboxes"
        title="No responses yet"
        description="Once a published survey collects its first response, it will show up here."
    />
@else
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Survey</th>
                    <th>Client</th>
                    <th>Sentiment</th>
                    <th>Completed</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recentResponses as $response)
                    <tr class="ds-fade-in">
                        <td>
                            @if ($response->survey)
                                <a href="{{ route('admin.surveys.edit', $response->survey) }}" class="text-decoration-none fw-semibold">
                                    {{ $response->survey->title }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $response->client?->company_name ?? '—' }}</td>
                        <td>
                            <span class="badge text-bg-{{ $response->sentiment === 'positive' ? 'success' : ($response->sentiment === 'negative' ? 'danger' : 'secondary') }}">
                                {{ $response->sentiment ? ucfirst($response->sentiment) : '—' }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $response->completed_at?->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
