<div class="modal-header">
    <h5 class="modal-title">{{ $response->survey->title }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <div class="d-flex flex-wrap gap-3 mb-3 small text-muted">
        <span><i class="bi bi-flag"></i> {{ ucfirst($response->status) }}</span>
        @if ($response->sentiment)
            <span class="badge text-bg-{{ $response->sentiment === 'positive' ? 'success' : ($response->sentiment === 'negative' ? 'danger' : 'secondary') }}">{{ ucfirst($response->sentiment) }}</span>
        @endif
        <span><i class="bi bi-phone"></i> {{ $response->device ?? '—' }}</span>
        <span><i class="bi bi-globe"></i> {{ $response->browser ?? '—' }}</span>
        <span><i class="bi bi-signpost"></i> {{ $response->source ? ucfirst($response->source) : '—' }}</span>
        <span><i class="bi bi-clock"></i> Started {{ $response->started_at?->format('M j, Y g:ia') }}</span>
        @if ($response->completed_at)
            <span><i class="bi bi-check2"></i> Completed {{ $response->completed_at->format('M j, Y g:ia') }}</span>
        @endif
    </div>

    <ul class="list-group list-group-flush">
        @forelse ($response->answers as $answer)
            <li class="list-group-item">
                <div class="fw-semibold small mb-1">{{ $answer->question->question_text }}</div>
                <div>
                    @if (is_array($answer->answer))
                        {{ implode(', ', $answer->answer) }}
                    @else
                        {{ $answer->answer }}
                    @endif
                    @if (! is_null($answer->score))
                        <span class="badge text-bg-light text-dark border ms-1">score {{ $answer->score }}</span>
                    @endif
                </div>
            </li>
        @empty
            <li class="list-group-item text-muted small">No answers recorded for this response.</li>
        @endforelse
    </ul>
</div>
